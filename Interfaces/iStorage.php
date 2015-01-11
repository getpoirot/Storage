<?php
namespace Poirot\Storage\Interfaces;

use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Storage\Adapter\AbstractOptions;

interface iStorage extends EntityInterface
{
    /**
     * Storage Options
     *
     * @return AbstractOptions
     */
    function options();

    /**
     * Prepare Storage
     *
     * @return $this
     */
    function prepare();

    /**
     * Is Initialized?
     *
     * @return boolean
     */
    function isPrepared();

    /**
     * Destroy Current Ident Entities
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
