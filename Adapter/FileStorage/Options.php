<?php
namespace Poirot\Storage\Adapter\FileStorage;

use Poirot\Storage\Adapter\AbstractOptions;
use Poirot\Storage\Adapter\ArrayFileStorage;

class Options extends AbstractOptions
{
    protected $storagePath;

    /**
     * @var ArrayFileStorage
     */
    protected $adapter;

    /**
     * @return mixed
     */
    public function getStoragePath()
    {
        return rtrim($this->storagePath, DIRECTORY_SEPARATOR);
    }

    /**
     * @param mixed $storagePath
     */
    public function setStoragePath($storagePath)
    {
        $this->storagePath = $storagePath;
    }

    /**
     * Set Storage Identity
     *
     * - if ident has changed write down old changed data
     *
     * @param string $identity Storage Identity
     *
     * @return $this
     */
    function setIdent($identity)
    {
        if ($this->ident != null && $this->ident == $identity)
            // Nothing Changed
            return $this;

        if ($this->ident != null)
            // If Ident Change Write Down Current Data
            $this->adapter->writeDown();

        $this->ident = $identity;
        $this->adapter->loadDataFromFile();

        return $this;
    }

    /**
     * Inject Storage Adapter
     *
     * @param ArrayFileStorage $adapter
     *
     * @return $this
     */
    function setAdapter(ArrayFileStorage $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }
}
