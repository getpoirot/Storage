<?php
namespace Poirot\Storage\Adapter;

use Poirot\Core\AbstractOptions as BaseOptions;

class StorageOptions extends BaseOptions
{
    /**
     * @var string
     */
    protected $ident;

    /**
     * Set Storage Identity
     *
     * @param string $identity Storage Identity
     *
     * @return $this
     */
    function setIdent($identity)
    {
        $this->ident = $identity;

        return $this;
    }

    /**
     * Get Current Storage Identity
     *
     * @return string
     */
    function getIdent()
    {
        return $this->ident;
    }
}
 