<?php
namespace Poirot\Storage\Interfaces;

use Poirot\Core\Interfaces\iPoirotEntity;

interface iStorageGateway extends iPoirotEntity
{
    /**
     * Set Storage Domain Realm
     *
     * @param string $realm Storage Identity
     *
     * @return $this
     */
    function setRealm($realm);

    /**
     * Get Current Storage Realm Domain
     *
     * @return string
     */
    function getRealm();
}
