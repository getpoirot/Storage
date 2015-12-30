<?php
namespace Poirot\Storage;

use Poirot\Core\BuilderSetterTrait;
use Poirot\Core\Entity;
use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Core\Interfaces\iDataField;
use Poirot\Storage\Adapter\MemoryGateway;
use Poirot\Storage\Interfaces\iStorage;
use Poirot\Storage\Interfaces\iStorageGateway;

class Storage implements iStorage
{
    use BuilderSetterTrait;

    /** @var iDataField Meta Data */
    protected $meta;
    /** @var iStorageGateway */
    protected $gateway;


    /**
     * Data Gateway
     *
     * @return iStorageGateway
     */
    function gateway()
    {
        if (!$this->gateway)
            $this->gateway = new MemoryGateway;

        return $this->gateway;
    }

    // ...


    /**
     * Set Data Gateway
     *
     * @param iStorageGateway $gateway
     *
     * @return $this
     */
    function setGateway(iStorageGateway $gateway)
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
