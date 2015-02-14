<?php
namespace Poirot\Storage\Adapter\ArrayFile;

use Poirot\Storage\Adapter\ArrayFileStorage;
use Poirot\Storage\StorageOptions;

class ArrayFileOptions extends StorageOptions
{
    protected $storagePath;

    /**
     * @var ArrayFileStorage
     */
    protected $adapter;

    /**
     * @return mixed
     */
    function getStoragePath()
    {
        return rtrim($this->storagePath, DIRECTORY_SEPARATOR);
    }

    /**
     * @param mixed $storagePath
     */
    function setStoragePath($storagePath)
    {
        $this->storagePath = $storagePath;
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
