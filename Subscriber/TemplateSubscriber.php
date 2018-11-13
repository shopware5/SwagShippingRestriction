<?php

namespace SwagShippingRestriction\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use SwagShippingRestriction\Services\VersionCheck;

class TemplateSubscriber implements SubscriberInterface
{
    /**
     * @var string
     */
    private $viewDir;

    /**
     * @var string
     */
    private $version;

    /**
     * @param string $viewDir
     * @param string $version
     */
    public function __construct($viewDir, $version)
    {
        $this->viewDir = $viewDir;
        $this->version = $version;
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
        if (!VersionCheck::isActive($this->version)) {
            return;
        }

        $args->get('subject')->View()->addTemplateDir($this->viewDir);
    }
}