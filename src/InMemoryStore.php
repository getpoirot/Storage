<?php
namespace Poirot\Storage;

use Poirot\Storage\Exception\exDataMalformed;
use Poirot\Storage\Exception\exDataNotPersistable;
use Poirot\Storage\Exception\exInvalidKey;
use Poirot\Storage\Exception\Storage\exIOError;


class InMemoryStore
    extends aDataStore
{
    protected $data = [];


    /**
     * Set Entity
     *
     * - Accept any serializable value
     *
     *
     * @param mixed $key Entity Key
     * @param mixed|null $value Value, Serializable.
     *                          NULL value for a property considered __isset false
     *
     * @return $this
     * @throws exDataNotPersistable|exInvalidKey|exIOError
     */
    function set($key, $value)
    {
        \Poirot\Storage\assertKey($key);

        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get Entity Value
     *
     * @param mixed $key Entity Key
     * @param null $default Default If Not Value/Key Exists
     *
     * @return mixed|null
     * @throws exIOError
     */
    function get($key, $default = null)
    {
        \Poirot\Storage\assertKey($key);

        return ( isset($this->data[$key]) ) ? $this->data[$key] : $default;
    }

    /**
     * Retrieve the values of multiple keys
     *
     * @param array $keys
     *
     * @return \Traversable|null
     * @throws exDataMalformed|exIOError|exInvalidKey
     */
    function getFromKeys(array $keys)
    {
        foreach ($keys as $k)
            yield $this->get($k);
    }

    /**
     * Remove key and associated value from store
     *
     * @param mixed $key
     *
     * @return void
     * @throws exInvalidKey|exIOError
     */
    function del($key)
    {
        unset( $this->data[$key] );
    }

    /**
     * Determine Given Key Is Exists In Storage?
     *
     * @param mixed $key
     *
     * @return bool
     * @throws exInvalidKey|exIOError
     */
    function has($key)
    {
        return ( isset($this->data[$key]) );
    }

    /**
     * Clear All Key/Values Stored
     *
     * @return void
     * @throws exIOError
     */
    function clean()
    {
        $this->data = [];
    }

    protected function doDestroy()
    {
        // Just demonstrate destroy!!
        unset( $this->data );
    }


    // Implement Iterator

    /**
     * Retrieve an external iterator
     * @return \Traversable
     */
    function getIterator()
    {
        foreach ($this->data as $key => $value)
            yield $key => $value;
    }


    // Implement Countable

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    function count()
    {
        return count( $this->data );
    }
}
