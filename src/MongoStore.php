<?php
namespace Poirot\Storage;

use MongoDB\Collection;
use MongoDB\Exception\UnexpectedValueException;
use Poirot\Std\Exceptions\exImmutable;
use Poirot\Storage\Exception\exDataMalformed;
use Poirot\Storage\Exception\exDataNotPersistable;
use Poirot\Storage\Exception\exInvalidKey;
use Poirot\Storage\Exception\Storage\exIOError;
use Poirot\Storage\Exception\Storage\exReadError;
use Poirot\Storage\Exception\Storage\exWriteError;
use Poirot\Storage\Interchange\SerializeInterchange;
use Poirot\Storage\Interfaces\iInterchangeable;


class MongoStore
    extends InMemoryStore
{
    /** @var Collection */
    protected $collection;
    /** @var iInterchangeable */
    protected $interchange;

    private static $typeMap = [
        'root' => 'array',
        'document' => 'array',
        'array' => 'array',
    ];


    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        if (! $this->collection )
            throw new \Exception('No Mongo Collection Is Given While Initialize Data Store.');

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
        parent::set($key, $value);


        $key   = (string) $key;
        $value = $this->getDataInterchange()->makeForward($value);
        $realm = $this->getRealm();

        try {
            $this->collection->replaceOne(
                [
                    'key'   => $key,
                    'realm' => $realm,
                ]
                , [ 'key' => $key, 'value' => $value, 'realm' => $realm ]
                , [ 'upsert' => true ]
            );
        } catch (UnexpectedValueException $e) {
            throw new exDataMalformed($e->getMessage(), $e->getCode(), $e);
        } catch (\Exception $e) {
            throw new exWriteError('Error While Write To Mongo Client.', $e->getCode(), $e);
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
     * @throws exDataMalformed|exInvalidKey|exIOError
     */
    function get($key, $default = null)
    {
        if ( $r = parent::get($key, null) )
            return $r;


        try {
            $document = $this->collection->findOne(
                [
                    'key'   => $key,
                    'realm' => $this->getRealm(),
                ]
                , [ 'typeMap' => self::$typeMap ] // override typeMap option
            );
        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }

        $r = (null === $document)
            ? $default
            : $this->getDataInterchange()->retrieveBackward($r['value']);


        parent::set($key, $r);
        return $r;
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
        try {
            $c = $this->collection->find(
                [
                    'key'   => [ '$in' => array_values($keys) ],
                    'realm' => $this->getRealm(),
                ]
                , [ 'typeMap' => self::$typeMap ] // override typeMap option
            );

            foreach ($c as $d) {
                $key     = $d['key'];
                $val     = $this->getDataInterchange()->retrieveBackward( $d['value'] );

                parent::set($key, $val);
                yield $key => $val;
            }

        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }

        yield;
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
        parent::del($key);

        try {
            $this->collection->deleteOne([
                'key'   => $key,
                'realm' => $this->getRealm(),
            ]);
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
     * @throws exInvalidKey|exIOError
     */
    function has($key)
    {
        \Poirot\Storage\assertKey($key);

        try {
            $count = $this->collection->count([
                'key'   => $key,
                'realm' => $this->getRealm(),
            ]);
        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }

        return $count > 0;
    }

    /**
     * Clear All Key/Values Stored
     *
     * @return void
     * @throws exIOError
     */
    function clean()
    {
        try {
            $this->collection->drop();
        } catch (\Exception $e) {
            throw new exWriteError($e->getMessage(), $e->getCode(), $e);
        }
    }


    // ..

    protected function doDestroy()
    {
        // do nothing
    }


    // Options:

    /**
     * Get Mongo Collection Instance
     *
     * @return Collection
     */
    function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set Mongo Collection Instance
     *
     * @param Collection $collection
     *
     * @return $this
     */
    function giveCollection(Collection $collection)
    {
        if ($this->collection)
            throw new exImmutable;


        $this->collection = $collection;
        return $this;
    }

    function giveDataInterchange(iInterchangeable $interchange)
    {
        if ($this->interchange)
            throw new exImmutable('Data Interchange is Immutable and given before.');

        $this->interchange = $interchange;
        return $this;
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


    // Implement Iterator

    /**
     * Retrieve an external iterator
     * @return \Traversable
     */
    function getIterator()
    {
        try {
            $c = $this->collection->find(
                [
                    'realm' => $this->getRealm(),
                ]
                , [
                    'projection' => ['key' => 1, 'value' => 1],
                    'typeMap'    => self::$typeMap
                 ]
            );

            foreach ($c as $d)
                yield $d['key'] => $d['value'];

        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }

        yield;
    }


    // Implement Countable

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    function count()
    {
        try {
            $count = $this->collection->count(
                [
                    'realm' => $this->getRealm(),
                ]
            );
        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }

        return $count;
    }
}
