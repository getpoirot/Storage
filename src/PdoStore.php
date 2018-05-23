<?php
namespace Poirot\Storage;

use Poirot\Std\Exceptions\exImmutable;
use Poirot\Storage\Exception\exDataMalformed;
use Poirot\Storage\Exception\exDataNotPersistable;
use Poirot\Storage\Exception\exInvalidKey;
use Poirot\Storage\Exception\Storage\exIOError;
use Poirot\Storage\Exception\Storage\exReadError;
use Poirot\Storage\Exception\Storage\exWriteError;
use Poirot\Storage\Interchange\SerializeInterchange;
use Poirot\Storage\Interfaces\iInterchangeable;


class PdoStore
    extends InMemoryStore
{
    /** @var \PDO */
    protected $pdo;
    /** @var iInterchangeable */
    protected $interchange;


    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        if (! $this->pdo )
            throw new \Exception('No PDO Conn. Is Given While Initialize Data Store.');

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
        $realm = $this->getRealm();
        $value = $this->getDataInterchange()->makeForward($value);

        try {

            // Find
            //
            $sql = "BEGIN;";
            $sql.= "DELETE FROM `KVStore` WHERE `realm` = '$realm' and `key` = '$key';";
            $sql.= "INSERT INTO `KVStore` (`realm`, `key`, `value`) VALUES ('$realm', '$key', '$value');";
            $sql.= "COMMIT;";

            $this->pdo->exec($sql);

        } catch (\Exception $e) {
            throw new exWriteError('Error While Write To Pdo Client.', $e->getCode(), $e);
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


        $realm = $this->getRealm();

        try {

            $sql = "SELECT * FROM `KVStore` WHERE `realm` = '$realm' and `key` = '$key';";

            $stm = $this->pdo->prepare($sql);
            $stm->execute();
            $stm->setFetchMode(\PDO::FETCH_ASSOC);

            $document = $stm->fetch();

        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }

        $r = (null === $document)
            ? $default
            : $this->getDataInterchange()->retrieveBackward($document['value']);


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
        $realm = $this->getRealm();


        foreach ($keys as $i => $k)
            $keys[$i] = "'$k'";

        $keys = implode(', ', $keys);

        try {
            $sql = "SELECT * FROM `KVStore` WHERE `realm` = '$realm' and `key` IN ($keys);";

            $stm = $this->pdo->prepare($sql);
            $stm->execute();
            $stm->setFetchMode(\PDO::FETCH_ASSOC);

            $c = $stm->fetchAll();

            foreach ($c as $d) {
                $key     = $d['key'];
                $val     = $this->getDataInterchange()->retrieveBackward( $d['value'] );

                parent::set($key, $val);
                yield $key => $val;
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
     * @throws exInvalidKey|exIOError
     */
    function del($key)
    {
        parent::del($key);


        $realm = $this->getRealm();

        try {

            $sql = "DELETE FROM `KVStore` WHERE `realm` = '$realm' and `key` = '$key';";
            $this->pdo->exec($sql);

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


        $realm = $this->getRealm();

        try {

            $sql = "SELECT COUNT(*) as has_key FROM `KVStore` WHERE `realm` = '$realm' and `key` = '$key';";
            $stm = $this->pdo->prepare($sql);
            $stm->execute();
            $stm->setFetchMode(\PDO::FETCH_ASSOC);

            $count = $stm->fetch();
            $count = (int) $count['has_key'];

        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }

        return ($count > 0);
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

            $sql = "DELETE FROM `KVStore` WHERE 1;";
            $this->pdo->exec($sql);

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
     * Get PDO Instance
     *
     * @return \PDO
     */
    function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Set PDO Instance
     *
     * @param \PDO $conn
     *
     * @return $this
     */
    function givePdo(\PDO $conn)
    {
        if ($this->pdo)
            throw new exImmutable;


        $this->pdo = $conn;
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
        // TODO improve iteration by pagination

        $realm = $this->getRealm();


        try {
            $sql = "SELECT * FROM `KVStore` WHERE `realm` = '$realm';";

            $stm = $this->pdo->prepare($sql);
            $stm->execute();
            $stm->setFetchMode(\PDO::FETCH_ASSOC);

            $c = $stm->fetchAll();

            foreach ($c as $d)
                yield $d['key'] => $this->getDataInterchange()->retrieveBackward( $d['value'] );

        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }
    }


    // Implement Countable

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    function count()
    {
        $realm = $this->getRealm();


        try {
            $sql = "SELECT COUNT(*) as has_key FROM `KVStore` WHERE `realm` = '$realm';";
            $stm = $this->pdo->prepare($sql);
            $stm->execute();
            $stm->setFetchMode(\PDO::FETCH_ASSOC);

            $count = $stm->fetch();
            $count = (int) $count['has_key'];

        } catch (\Exception $e) {
            throw new exReadError($e->getMessage(), $e->getCode(), $e);
        }

        return $count;
    }
}
