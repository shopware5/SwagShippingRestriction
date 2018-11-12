<?php

namespace SwagShippingRestriction\Subscriber;


use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagShippingRestriction\Services\CountryNotAvailableService;

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
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Frontend_Address' => 'onPostDispatchAddress'
        ];
    }

    public function __construct(CountryNotAvailableService $service, ContextServiceInterface $contextService)
    {
        $this->service = $service;
        $this->contextService = $contextService;
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onPostDispatchAddress(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();

        $countries = $this->service->getNotAvailableCountries($this->contextService->getContext());
        sort($countries);

        $view->assign('notAvailableCountries', $countries);
    }
}