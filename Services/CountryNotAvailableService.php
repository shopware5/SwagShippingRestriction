<?php

namespace SwagShippingRestriction\Services;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CountryNotAvailableService
{
    /**
     * @var CountryGatewayInterface
     */
    private $countryGateway;

    /**
     * @var \Zend_Cache_Core
     */
    private $cache;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param CountryGatewayInterface $countryGateway
     * @param \Zend_Cache_Core $cache
     * @param Connection $connection
     */
    public function __construct(CountryGatewayInterface $countryGateway, \Zend_Cache_Core $cache, Connection $connection)
    {
        $this->countryGateway = $countryGateway;
        $this->cache = $cache;
        $this->connection = $connection;
    }

    /**
     * @param int $countryId
     * @param ShopContextInterface $context
     * @return bool
     */
    public function isCountryAllowed($countryId, ShopContextInterface $context)
    {
        $key = $this->getCacheKey($countryId, $context);
        if ($allowed = $this->cache->load($key)) {
            return $allowed;
        }

        $country = $this->countryGateway->getCountry($countryId, $context);

        $allowed = $this->isAllowedCountry($country);

        return $allowed;
    }

    /**
     * @param ShopContextInterface $context
     * @return Country[]
     */
    public function getNotAvailableCountries(ShopContextInterface $context)
    {
        $ids = $this->connection->executeQuery('SELECT id FROM s_core_countries')->fetchAll(\PDO::FETCH_COLUMN);
        $countries = $this->countryGateway->getCountries($ids, $context);

        return array_filter(array_map(function(Country $country) {
            return !$this->isAllowedCountry($country) ? $country->getName() : null;
        }, $countries));
    }

    /**
     * @param int $countryId
     * @param ShopContextInterface $context
     * @return string
     */
    private function getCacheKey($countryId, ShopContextInterface $context)
    {
        return md5(sprintf('country_allowed_%d_%d', $countryId, $context->getShop()->getId()));
    }

    /**
     * @param Country $country
     * @return bool
     */
    private function isAllowedCountry(Country $country)
    {
        return !$country->hasAttribute('core') ? true : $country->getAttribute('core')->get('swag_allow_shipping');
    }
}