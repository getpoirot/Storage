<?php
namespace Poirot\Storage\Interfaces;

use Poirot\Std\Interfaces\Struct\iDataEntity;
use Poirot\Storage\Exception\exDataMalformed;
use Poirot\Storage\Exception\exDataNotPersistable;
use Poirot\Storage\Exception\exInvalidKey;
use Poirot\Storage\Exception\Storage\exIOError;


interface iDataStore
    extends iDataEntity
{
    /**
     * iDataStore constructor.
     *
     * @param string $realm Storage Identity
     */
    function __construct($realm);


    /**
     * Get Current Storage Realm Domain
     *
     * @return string
     */
    function getRealm();

    /**
     * Set Entity
     *
     * - Accept any serializable value
     *
     *
     * @param mixed      $key   Entity Key
     * @param mixed|null $value Value, Serializable.
     *                          NULL value for a property considered __isset false
     *
     * @return $this
     * @throws exDataNotPersistable|exInvalidKey|exIOError
     */
    function set($key, $value);

    /**
     * Get Entity Value
     *
     * @param mixed $key     Entity Key
     * @param null  $default Default If Not Value/Key Exists
     *
     * @return mixed|null
     * @throws exDataMalformed|exInvalidKey|exIOError
     */
    function get($key, $default = null);

    /**
     * Retrieve the values of multiple keys
     *
     * @param array $keys
     *
     * @return \Traversable|null
     * @throws exDataMalformed|exIOError|exInvalidKey
     */
    function getFromKeys(array $keys);

    /**
     * Remove key and associated value from store
     *
     * @param mixed $key
     *
     * @return void
     * @throws exInvalidKey|exIOError
     */
    function del($key);

    /**
     * Determine Given Key Is Exists In Storage?
     *
     * @param mixed $key
     *
     * @return bool
     * @throws exInvalidKey|exIOError
     */
    function has($key);

    /**
     * Clear All Key/Values Stored
     *
     * @return void
     * @throws exIOError
     */
    function clean();

    /**
     * Destroy Current Realm Data Source
     *
     * @return void
     */
    function destroy();
}
