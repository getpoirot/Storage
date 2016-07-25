<?php
namespace Poirot\Storage\Gateway;

use Poirot\Std\Interfaces\Struct\iDataEntity;
use Poirot\Std\Struct\DataPointerArray;

/*
$s = new P\Storage\Gateway\DataStorageSession('my_realm');
$s->setData([
    'name'   => 'Payam',
    'family' => 'Naderi',
]);

$s->setRealm('new');
print_r(P\Std\cast($s)->toArray()); // Array ( )

$s->setRealm('my_realm');
$s->import(['email'  => 'naderi.payam@gmail.com']);
print_r(P\Std\cast($s)->toArray()); // Array ( [name] => Payam [family] => Naderi [email] => naderi.payam@gmail.com )


// You can see sessions are properly set on other page
session_start()
var_dump($_SESSION);
*/


class DataStorageSession
    extends DataStorageBase
{
    /** @var boolean */
    protected $isPrepared = false;

    
    // ..
    
    /**
     * @return iDataEntity
     */
    protected function _newDataStorage()
    {
        $this->_prepareSession();
        
        $realm = $this->getRealm();
        if (!isset($_SESSION[$realm]))
            $_SESSION[$realm] = array();

        return new DataPointerArray($_SESSION[$realm]);
    }

    /**
     * Prepare Storage
     *
     * @throws \Exception
     * @return $this
     */
    protected function _prepareSession()
    {
        if ($this->isPrepared)
            return;

        if (!$this->_assertSessionRestriction())
            // TODO start session can be implemented if any session data __set
            // other wise it seems not neccessary to start sesion if no data
            // read/write happend
            session_start();

        $this->isPrepared = true;
    }

    /**
     * Does a session exist and is it currently active?
     *
     * @return bool
     */
    protected function _assertSessionRestriction()
    {
        if ( php_sapi_name() !== 'cli' ) {
            if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                return session_status() === PHP_SESSION_ACTIVE ? true : false;
            } else {
                return session_id() === '' ? false : true;
            }
        }

        return false;
    }
}
