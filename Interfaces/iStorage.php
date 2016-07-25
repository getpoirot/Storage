<?php
namespace Poirot\Storage\Interfaces;

use Poirot\Std\Interfaces\Pact\ipMetaProvider;
use Poirot\Std\Interfaces\Struct\iDataMean;

interface iStorage 
    extends ipMetaProvider
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
     * @return iDataMean
     */
    function meta();
}
