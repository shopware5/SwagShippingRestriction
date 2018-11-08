<?php

namespace SwagShippingRestriction\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\Models\Customer\Customer;
use SwagShippingRestriction\Services\CountryNotAvailableService;

class RegisterSubscriber implements SubscriberInterface
{
    /**
     * @var CountryNotAvailableService
     */
    private $availableService;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_Frontend_Register_saveRegister' => 'onSaveRegister'
        ];
    }

    /**
     * @param CountryNotAvailableService $availableService
     */
    public function __construct(CountryNotAvailableService $availableService)
    {
        $this->availableService = $availableService;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onSaveRegister(Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $request = $controller->Request();
        $view = $controller->View();

        $data = $this->getPostData($request);

        if ($this->isShippingProvided($data)) {
            return;
        }

        $countryId = $data['register']['billing']['country'];

        $allowed = $this->availableService->isCountryAllowed($countryId, $controller->get('shopware_storefront.context_service')->getContext());

        if ($allowed) {
            return;
        }

        $errors = [
            'personal' => [],
            'billing' => [
                'country' => $controller->get('snippets')->getNamespace('frontend/register/index')->get('CountryNotAvailableForShipping')
            ],
            'shipping' => []
        ];

        $view->assign('errors', $errors);
        $view->assign($data);

        $controller->forward('index');

        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function isShippingProvided(array $data)
    {
        return array_key_exists('shippingAddress', $data['register']['billing']);
    }

    /**
     * @param \Enlight_Controller_Request_Request $request
     * @return array
     */
    private function getPostData(\Enlight_Controller_Request_Request $request)
    {
        $data = $request->getPost();

        $countryStateName = "country_state_" . $data['register']['billing']['country'];
        $data['register']['billing']['state'] = $data['register']['billing'][$countryStateName];

        $countryStateName = "country_state_" . $data['register']['shipping']['country'];
        $data['register']['shipping']['state'] = $data['register']['shipping'][$countryStateName];
        $data['register']['billing'] += $data['register']['personal'];
        $data['register']['shipping']['phone'] = $data['register']['personal']['phone'];

        if (!$data['register']['personal']['accountmode']) {
            $data['register']['personal']['accountmode'] = Customer::ACCOUNT_MODE_CUSTOMER;
        }

        $data['register']['billing']['additional']['customer_type'] = $data['register']['personal']['customer_type'];

        return $data;
    }
}