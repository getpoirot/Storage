<?php
namespace Poirot\Storage\Adapter\FileStorage;

use Poirot\Storage\Adapter\ArrayFileStorage;
use Poirot\Storage\StorageOptions;

class FileStorageOptions extends StorageOptions
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
