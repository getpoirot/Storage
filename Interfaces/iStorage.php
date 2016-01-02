<?php
namespace Poirot\Storage\Interfaces;

use Poirot\Core\Interfaces\iDataField;
use Poirot\Core\Interfaces\iMetaProvider;

interface iStorage extends iMetaProvider
{
    /**
     * Data Gateway
     *
     * @return iStorageData
     */
    function data();


    // Meta Provider:

    /**
     * Get Meta Data Entity Object
     *
     * - use to access meta extra data over storage,
     *   basically used by storage decorators
     *
     * @return iDataField
     */
    function meta();
}
