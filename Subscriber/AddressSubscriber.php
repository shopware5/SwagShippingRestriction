<?php

namespace SwagShippingRestriction\Subscriber;


use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagShippingRestriction\Services\CountryNotAvailableService;
use SwagShippingRestriction\Services\VersionCheck;

class AddressSubscriber implements SubscriberInterface
{
    /**
     * @var CountryNotAvailableService
     */
    private $service;
    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var string
     */
    private $version;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Frontend_Address' => 'onPostDispatchAddress'
        ];
    }

    /**
     * @param CountryNotAvailableService $service
     * @param ContextServiceInterface $contextService
     * @param string $version
     */
    public function __construct(CountryNotAvailableService $service, ContextServiceInterface $contextService, $version)
    {
        $this->service = $service;
        $this->contextService = $contextService;
        $this->version = $version;
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchAddress(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();

        if (!VersionCheck::isActive($this->version)) {
            return;
        }

        $countries = $this->service->getNotAvailableCountries($this->contextService->getContext());
        sort($countries);

        $view->assign('notAvailableCountries', $countries);
    }
}