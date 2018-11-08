<?php

namespace SwagShippingRestriction;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class SwagShippingRestriction
 * @package SwagShippingRestriction
 */
class SwagShippingRestriction extends Plugin
{
    /**
     * @param InstallContext $context
     * @throws \Exception
     */
    public function install(InstallContext $context)
    {
        $this->container->get('shopware_attribute.crud_service')->update('s_core_countries_attributes', 'swag_allow_shipping', 'boolean', [
            'displayInBackend' => true,
            'translatable' => true,
            'label' => 'Allow as Shipping Country'
        ]);

        $this->container->get('models')->generateAttributeModels(['s_core_countries_attributes']);

        // Fix default value
        $this->container->get('dbal_connection')->executeQuery('ALTER TABLE `s_core_countries_attributes` CHANGE `swag_allow_shipping` `swag_allow_shipping` int(1) NULL DEFAULT \'1\' AFTER `countryID`;');
        $this->container->get('dbal_connection')->executeQuery('UPDATE `s_core_countries_attributes` SET swag_allow_shipping = 1 WHERE swag_allow_shipping IS NULL');

        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }

    /**
     * @param UninstallContext $context
     * @throws \Exception
     */
    public function uninstall(UninstallContext $context)
    {
        if ($context->keepUserData()) {
            return;
        }

        $this->container->get('shopware_attribute.crud_service')->delete('s_core_countries_attributes', 'swag_allow_shipping');

        $this->container->get('models')->generateAttributeModels(['s_core_countries_attributes']);
    }

    /**
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    /**
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->setParameter('swag_shipping_restriction.plugin_dir', $this->getPath());
    }
}