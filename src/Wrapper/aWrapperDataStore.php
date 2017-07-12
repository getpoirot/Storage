<?php
namespace Poirot\Storage\Wrapper;

use Poirot\Storage\Exception\Storage\exIOError;
use Poirot\Storage\exDataMalformed;
use Poirot\Storage\exDataNotPersistable;
use Poirot\Storage\exInvalidKey;
use Poirot\Storage\Interfaces\iDataStore;
use Traversable;


class aWrapperDataStore
    implements iDataStore
{
    /** @var iDataStore */
    protected $datastore;


    /**
     * iDataStore constructor.
     *
     * @param iDataStore $dataStore Wrapper Around
     */
    function __construct($dataStore)
    {
        $this->datastore = $dataStore;
    }


    /**
     * Get Current Storage Realm Domain
     *
     * @return string
     */
    function getRealm()
    {
        return $this->datastore->getRealm();
    }

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
        $this->datastore->set($key, $value);
        return $this;
    }

    /**
     * Get Entity Value
     *
     * @param mixed $key Entity Key
     * @param null $default Default If Not Value/Key Exists
     *
     * @return mixed|null
     * @throws exInvalidKey|exIOError
     */
    function get($key, $default = null)
    {
        return $this->datastore->get($key, $default);
    }

    /**
     * Retrieve the values of multiple keys
     *
     * @param array $keys
     *
     * @return \Traversable
     * @throws exDataMalformed|exIOError|exInvalidKey
     */
    function getFromKeys(array $keys)
    {
        return $this->datastore->getFromKeys($keys);
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
        $this->datastore->del($key);
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
        return $this->datastore->has($key);
    }

    /**
     * Set Struct Data From Array
     *
     * @param array|\Traversable|null $data
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function import($data)
    {
        $this->datastore->import($data);
        return $this;
    }

    /**
     * Is Empty?
     * @return bool
     */
    function isEmpty()
    {
        return $this->datastore->isEmpty();
    }

    /**
     * Clear All Key/Values Stored
     *
     * @return void
     * @throws exIOError
     */
    function clean()
    {
        $this->datastore->clean();
    }

    /**
     * Destroy Current Realm Data Source
     *
     * @return void
     */
    function destroy()
    {
        $this->datastore->destroy();
    }


    // Implement Iterator

    /**
     * Retrieve an external iterator
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     */
    function getIterator()
    {
        foreach ($this->datastore as $key => $val)
            yield $key => $val;
    }


    // Implement Countable

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    function count()
    {
        return $this->datastore->count();
    }

}
