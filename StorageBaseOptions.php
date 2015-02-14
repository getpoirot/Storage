<?php
namespace Poirot\Storage;

use Poirot\Core\AbstractOptions as BaseOptions;

class StorageBaseOptions extends BaseOptions
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
 