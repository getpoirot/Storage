<?php
namespace Poirot\Storage\Interchange;

use Poirot\Storage\exUnknownData;
use Poirot\Storage\Interfaces\iInterchangeable;


// TODO
class JsonInterchange
    implements iInterchangeable
{
    /**
     * Interchange Given Data To Persistence Model
     *
     * note: binary string which may include null bytes,
     *       and needs to be stored and handled as such.
     *       output should generally be stored in a BLOB field in a database,
     *       rather than a CHAR or TEXT field.
     *
     * @param mixed $value
     *
     * @return mixed|string
     * @throws exUnknownData
     */
    function makeForward($value)
    {
        $interchangeable = json_encode($value);
        return $interchangeable;
    }

    /**
     * Retrieve Back Interchangeable Data
     *
     * @param mixed $data
     *
     * @return mixed
     */
    function retrieveBackward($data)
    {
        $value = json_decode($data);
        return $value;
    }
}
