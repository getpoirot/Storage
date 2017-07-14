<?php
namespace Poirot\Storage
{
    use Poirot\Storage\Exception\exInvalidKey;


    /**
     * Assert Given Key
     *
     * @param $key
     *
     * @throws exInvalidKey
     */
    function assertKey($key)
    {
        if ( !is_string($key) && !is_int($key) )
            throw new exInvalidKey(sprintf(
                'Key must be string or int; given: (%s).'
                , \Poirot\Std\flatten($key)
            ));
    }

}
