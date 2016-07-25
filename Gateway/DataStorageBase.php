<?php
namespace Poirot\Storage\Gateway;

use Poirot\Storage\Interfaces\iStorageData;

use Poirot\Std\ConfigurableSetter;
use Poirot\Std\Interfaces\Struct\iDataEntity;
use Poirot\Std\Struct\DataEntity;

/*
$s = new P\Storage\Gateway\DataStorageBase('my_realm');
$s->setData([
    'name'   => 'Payam',
    'family' => 'Naderi',
]);

$s->setRealm('new');
print_r(P\Std\cast($s)->toArray()); // Array ( )

$s->setRealm('my_realm');
$s->import(['email'  => 'naderi.payam@gmail.com']);
print_r(P\Std\cast($s)->toArray()); // Array ( [name] => Payam [family] => Naderi [email] => naderi.payam@gmail.com )
*/

class DataStorageBase 
    extends ConfigurableSetter
    implements iStorageData
{
    const REALM_DEFAULT = 'poirot_storage_realm';

    /** @var string Storage Domain Realm*/
    protected $realm;
    protected $realmStorage;


    /**
     * Construct
     *
     * @param string|array|\Traversable $realm   Realm or Setter Options
     * @param array|\Traversable        $setter  Setter Options
     */
    function __construct($realm = null, $setter = null)
    {
        if (is_string($realm))
            $this->setRealm($realm);
        else
            $setter = $realm;

        $this->putBuildPriority(array('realm'));
        parent::__construct($setter);
    }

    /**
     * Set Storage Domain Realm
     *
     * @param string $realm Storage Identity
     *
     * @return $this
     */
    function setRealm($realm)
    {
        $this->realm = (string) $realm;
        return $this;
    }

    /**
     * Get Current Storage Realm Domain
     *
     * @return string
     */
    function getRealm()
    {
        if (!$this->realm)
            $this->realm = self::REALM_DEFAULT;

        return $this->realm;
    }

    /**
     * Destroy Current Realm Data Source
     *
     * @return void
     */
    function destroy()
    {
        $this->clean();
    }

    
    // Options:
    
    /**
     * Set Data For This Realm
     *
     * @param $data
     *
     * @return $this
     */
    function setData($data)
    {
        $this->import($data);
        return $this;
    }
    
    
    // Implement iStorageData

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->_attainRealmDataStorage()->getIterator();
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->_attainRealmDataStorage()->count();
    }

    /**
     * Import Data Into Current Realm
     *
     * @param array|\Traversable|null $data
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function import($data)
    {
        $this->_attainRealmDataStorage()->import($data);
        return $this;
    }

    /**
     * Empty Values Of Current Realm
     * 
     * @return $this
     */
    function clean()
    {
        $this->_attainRealmDataStorage()->clean();
        return $this;
    }

    /**
     * Is Current Realm Empty?
     * 
     * @return bool
     */
    function isEmpty()
    {
        return $this->_attainRealmDataStorage()->isEmpty();
    }

    /**
     * Has Current Realm Given Key?
     * 
     * @param mixed $key
     * 
     * @return bool
     */
    function has($key)
    {
        return $this->_attainRealmDataStorage()->has($key);
    }

    /**
     * Delete Given Key From Current Realm
     * 
     * @param mixed $key
     * @return $this
     */
    function del($key)
    {
        $this->_attainRealmDataStorage()->del($key);
        return $this;
    }

    /**
     * Set Entity Key/Value Into Current Realm 
     *
     * - values that set to null must be unset from entity
     *
     * @param mixed $key Entity Key
     * @param mixed|null $value Entity Value
     *                          NULL value for a property considered __isset false
     *
     * @return $this
     */
    function set($key, $value)
    {
        $this->_attainRealmDataStorage()->set($key, $value);
        return $this;
    }

    /**
     * Get Entity Value From Current Realm
     *
     * @param mixed $key Entity Key
     * @param null $default Default If Not Value/Key Exists
     *
     * @throws \Exception Value not found
     * @return mixed|null NULL value for a property considered __isset false
     */
    function get($key, $default = null)
    {
        return $this->_attainRealmDataStorage()->get($key, $default);
    }
    
    
    // ..

    /**
     * @return iDataEntity
     */
    protected function _attainRealmDataStorage()
    {
        $realm = $this->getRealm();
        if (!isset($this->realmStorage[$realm]))
            $this->realmStorage[$realm] = $this->_newDataStorage();
        
        return $this->realmStorage[$realm];
    }

    /**
     * @return iDataEntity
     */
    protected function _newDataStorage()
    {
        return new DataEntity();
    }
}
