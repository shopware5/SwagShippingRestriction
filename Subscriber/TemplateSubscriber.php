<?php

namespace SwagShippingRestriction\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;

class TemplateSubscriber implements SubscriberInterface
{
    /**
     * @var string
     */
    private $viewDir;

    public function __construct($viewDir)
    {
        $this->viewDir = $viewDir;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'addTemplateDir',
            'Enlight_Controller_Action_PostDispatch_Widgets' => 'addTemplateDir',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function addTemplateDir(Enlight_Event_EventArgs $args)
    {
        $args->get('subject')->View()->addTemplateDir($this->viewDir);
    }
}