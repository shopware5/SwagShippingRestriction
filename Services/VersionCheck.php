<?php

namespace SwagShippingRestriction\Services;

class VersionCheck
{
    /**
     * @param string $version
     * @return bool
     */
    public static function isActive($version)
    {
        if ($version === '___VERSION___') {
            return false;
        }

        return version_compare($version, '5.5.2', '<=');
    }
}