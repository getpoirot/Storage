<?php
namespace Poirot\Storage;

use Poirot\Storage\Gateway\DataStorageMemory;
use Poirot\Storage\Interfaces\iStorage;
use Poirot\Storage\Interfaces\iStorageData;

use Poirot\Std\ConfigurableSetter;
use Poirot\Std\Interfaces\Struct\iDataMean;
use Poirot\Std\Struct\DataMean;

class Storage 
    extends ConfigurableSetter 
    implements iStorage
{
    /** @var iDataMean Meta Data */
    protected $meta;
    /** @var iStorageData */
    protected $gateway;


    /**
     * Data Gateway
     *
     * @return iStorageData
     */
    function data()
    {
        if (!$this->gateway)
            $this->gateway = new DataStorageMemory;

        return $this->gateway;
    }

    
    // ...

    /**
     * Set Data Gateway
     *
     * @param iStorageData $gateway
     *
     * @return $this
     */
    function setDataGateway(iStorageData $gateway)
    {
        $this->gateway = $gateway;
        return $this;
    }

    
    // Implement Meta Provider:

    /**
     * Get Meta Data Entity Object
     *
     * - use to access meta extra data over storage,
     *   basically used by storage decorators
     *
     * @return iDataMean
     */
    function meta()
    {
        if (!$this->meta)
            $this->meta = new DataMean();

        return $this->meta;
    }
}
