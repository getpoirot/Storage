<?php
namespace Poirot\Storage\Adapter\FileStorage;

use Poirot\Core\AbstractOptions;

class Options extends AbstractOptions
{
    protected $storagePath;

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
}
 