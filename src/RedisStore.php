<?php
namespace Poirot\Storage;


use Poirot\Storage\Exception\exDataMalformed;
use Poirot\Storage\Exception\exDataNotPersistable;
use Poirot\Storage\Exception\exInvalidKey;
use Poirot\Storage\Exception\Storage\exIOError;
use Poirot\Storage\Interfaces\iDataStore;
use Poirot\Storage\Interchange\SerializeInterchange;
use Poirot\Storage\Interfaces\iInterchangeable;
use Poirot\Storage\Exception\Storage\exReadError;
use Poirot\Storage\Exception\Storage\exWriteError;
use Poirot\Std\Exceptions\exImmutable;
use Predis\Client;
use Poirot\Storage\Redis\CleanScriptCommand;
use Poirot\Storage\Redis\CountScriptCommand;

class RedisStore extends aDataStore
{

    /** @var iInterchangeable */
    protected $interchange;

    /** @var  \Predis\Client */
    protected $client;

    protected function doDestroy()
    {
        // do nothing
    }

    /**
     * Retrieve an external iterator
     * @return \Traversable
     */
    function getIterator()
    {
        // TODO: maybe using hashes instead will solve the problem
        throw new \Exception("Not Implemented");
    }

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    function count()
    {
        try {
            $this->client->getProfile()->defineCommand('count', 'CountScriptCommand');
            return $this->client->count($this->_getPrefix().'*');
        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }
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
     * @throws exWriteError
     */
    function set($key, $value)
    {
        $serializedValue = $this->getDataInterchange()->makeForward($value);

        try {
            $this->client->set($this->_getKey($key), $serializedValue);
        } catch (\Exception $e) {
            throw new exWriteError('Error While Writing To Redis Client.', $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * Get Entity Value
     *
     * @param mixed $key Entity Key
     * @param null $default Default If Not Value/Key Exists
     *
     * @return mixed|null
     * @throws exReadError
     */
    function get($key, $default = null)
    {
        $value = null;
        try {
            $value = $this->client->get($this->_getKey($key));
        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }

        return (empty($value))
            ? $default
            : $this->getDataInterchange()->retrieveBackward($value);
    }

    /**
     * Retrieve the values of multiple keys
     *
     * @param array $keys
     *
     * @return \Traversable|null
     * @throws exReadError
     */
    function getFromKeys(array $keys)
    {
        $result = [];
        try {
            foreach ($keys as $key)
            {
                $result[] = $this->get($key);
            }
        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Remove key and associated value from store
     *
     * @param mixed $key
     *
     * @return void
     * @throws exWriteError
     */
    function del($key)
    {
        try {
            $this->client->del($this->_getKey($key));
        } catch (\Exception $e) {
            throw new exWriteError($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Determine Given Key Is Exists In Storage?
     *
     * @param mixed $key
     *
     * @return bool
     * @throws exReadError
     */
    function has($key)
    {
        try {
            return (bool) $this->client->exists($this->_getKey($key));
        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Clear All Key/Values Stored
     *
     * @return void
     * @throws exWriteError
     */
    function clean()
    {
        try {
            $this->client->getProfile()->defineCommand('cleanstore', 'CleanScriptCommand');
            $this->client->cleanstore($this->_getPrefix().'*');
        } catch (\Exception $e) {
            throw new exWriteError($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return iInterchangeable
     */
    function getDataInterchange()
    {
        if (! $this->interchange )
            $this->giveDataInterchange(new SerializeInterchange);

        return $this->interchange;
    }

    function giveDataInterchange(iInterchangeable $interchange)
    {
        if ($this->interchange)
            throw new exImmutable('Data Interchange is Immutable and given before.');

        $this->interchange = $interchange;
        return $this;
    }

    /**
     * Set Redis Client Instance
     *
     * @param \Predis\Client $client
     *
     * @return $this
     */
    function giveClient(\Predis\Client $client)
    {
        if ($this->client)
            throw new exImmutable;

        $this->client = $client;
        return $this;
    }

    /**
     * Get Redis Client Instance
     *
     * @return \Predis\Client
     */
    function getClient()
    {
        return $this->client;
    }

    /**
     * Get Prefix using Realm
     *
     * @return string
     */
    protected function _getPrefix()
    {
        $realm = $this->getRealm();
        return "{$realm}.";
    }

    /**
     * Gets Actual Key According to Realm
     *
     * @param string $key
     * @return string
     */
    protected function _getKey($key)
    {
        return $this->_getPrefix().$key;
    }

    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        if (! $this->client )
            throw new \Exception('No Redis Client Is Given While Initialize Data Store.');
    }

}