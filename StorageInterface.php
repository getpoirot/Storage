<?php
namespace Poirot\Storage;

use Poirot\Core\Interfaces\EntityInterface;

interface StorageInterface extends EntityInterface
{
    /**
     * Set Storage Identity
     *
     * @param string $identity Storage Identity
     *
     * @return $this
     */
    function setIdent($identity);

    /**
     * Get Current Storage Identity
     *
     * @return string
     */
    function getIdent();

    /**
     * Prepare Storage
     *
     * @return $this
     */
    function init();

    /**
     * Is Initialized?
     *
     * @return boolean
     */
    function isInit();

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
