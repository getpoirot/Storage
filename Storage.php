<?php
namespace Poirot\Storage;

use Poirot\Core\BuilderSetterTrait;
use Poirot\Core\Entity;
use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Core\Interfaces\iDataField;
use Poirot\Storage\Gateway\MemoryData;
use Poirot\Storage\Interfaces\iStorage;
use Poirot\Storage\Interfaces\iStorageData;

class Storage implements iStorage
{
    use BuilderSetterTrait;

    /** @var iDataField Meta Data */
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
            $this->gateway = new MemoryData;

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
     * @return EntityInterface
     */
    function meta()
    {
        if (!$this->meta)
            $this->meta = new Entity();

        return $this->meta;
    }
}
