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
