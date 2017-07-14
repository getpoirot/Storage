<?php
namespace Poirot\Storage\Interfaces;

use Poirot\Storage\Exception\exUnknownData;


interface iInterchangeable
{
    /**
     * Interchange Given Data To Persistence Model
     *
     * @param mixed $data
     *
     * @return mixed|string
     * @throws exUnknownData
     */
    function makeForward($data);

    /**
     * Retrieve Back Interchangeable Data
     *
     * @param mixed $data
     *
     * @return mixed
     */
    function retrieveBackward($data);
}
