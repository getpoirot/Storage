<?php
namespace Poirot\Storage\Interfaces;

use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Core\Interfaces\iEntityPoirot;
use Poirot\Storage\StorageBaseOptions;

interface iStorage extends iEntityPoirot
{
    /**
     * Storage Options
     *
     * @return StorageBaseOptions
     */
    function options();

    /**
     * Destroy Current Ident Entities
     *
     * - Ident relies on the options
     *
     * @return void
     */
    function destroy();

    /**
     * Get Meta Data Entity Object
     *
     * - use to access meta extra data over storage,
     *   basically used by storage decorators
     *
     * @return EntityInterface
     */
    function meta();
}
