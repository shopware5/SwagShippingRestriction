<?php

namespace SwagShippingRestriction\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Enlight_Hook_HookArgs;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Models\Customer\Customer;
use SwagShippingRestriction\Services\CountryNotAvailableService;

class CheckoutSubscriber implements SubscriberInterface
{
    /**
     * @var CountryNotAvailableService
     */
    private $availableService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'onPreDispatchCheckout',
            'Shopware_Controllers_Frontend_Checkout::getDispatchNoOrder::after' => 'afterGetDispatchNoOrder'
        ];
    }

    public function __construct(
        CountryNotAvailableService $availableService,
        Connection $connection,
        ContextServiceInterface $contextService
    ) {
        $this->availableService = $availableService;
        $this->connection = $connection;
        $this->contextService = $contextService;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatchCheckout(Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $request = $controller->Request();
        $view = $controller->View();

        if ($request->getActionName() !== 'confirm') {
            return;
        }

        $shippingId = (int) $view->getAssign('activeShippingAddressId');
        $billingId = (int) $view->getAssign('activeBillingAddressId');

        if (!$this->isValidAddress($shippingId)) {
            $view->assign('invalidShippingAddress', true);
            $view->assign('invalidShippingCountry', true);

            if ($shippingId === $billingId) {
                $view->assign('invalidBillingAddress', true);
            }
        }
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onPreDispatchCheckout(Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $request = $controller->Request();

        $session = $controller->get('session');
        $userData = $controller->get('modules')->Admin()->sGetUserData();

        if (
            (int) $userData['additional']['user']['accountmode'] === Customer::ACCOUNT_MODE_FAST_LOGIN &&
            !in_array($request->getActionName(), ['finish', 'confirm', 'index'])
        ) {
            $controller->get('config')->offsetSet('premiumshippingnoorder', false);
        }

        if ($request->getActionName() !== 'finish') {
            return;
        }

        if (empty($activeShippingAddressId = $session->offsetGet('checkoutShippingAddressId', null))) {
            $activeShippingAddressId = $userData['additional']['user']['default_shipping_address_id'];
        }

        if (!$this->isValidAddress($activeShippingAddressId)) {
            $controller->redirect([
                'controller' => 'checkout',
                'action' => 'confirm'
            ]);
        }
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     */
    public function afterGetDispatchNoOrder(Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();

        $accountMode = (int) $subject->View()->sUserData['additional']['user']['accountmode'];

        // Its the same as here https://github.com/shopware/shopware/commit/affcab35459c2a3528b113b0822214a757dd695f#diff-cd5eb2ecc938d8f4475bb285f52247ffR148
        $args->setReturn($accountMode === Customer::ACCOUNT_MODE_FAST_LOGIN && $args->getReturn());
    }

    /**
     * @param int $addressId
     * @return bool
     */
    private function isValidAddress($addressId)
    {
        $qb = $this->connection->createQueryBuilder();
        $countryId = $qb
            ->select('country_id')
            ->from('s_user_addresses', 'address')
            ->where('id = :id')
            ->setParameter('id', $addressId)
            ->execute()
            ->fetchColumn();

        return $this->availableService->isCountryAllowed($countryId, $this->contextService->getContext());
    }
}