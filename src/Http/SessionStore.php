<?php
namespace Poirot\Storage\Http;

use Poirot\Std\Struct\DataPointerArray;
use Poirot\Storage\InMemoryStore;


class SessionStore
    extends InMemoryStore
{
    /**
     * Initialize
     *
     */
    protected function __init()
    {
        parent::__init();

        $this->_assertSessionRestriction();
        if (! isset($_SESSION) )
            session_start();


        $realm = $this->getRealm();
        if (! isset($_SESSION[$realm]) )
            $_SESSION[$realm] = array();

        // PHP Allow direct change into global variable $_SESSION to manipulate data!
        $this->data = new DataPointerArray( $_SESSION[$realm] );
    }

    function destroy()
    {
        // Tell client to remove session data
        unset( $this->data[$this->getRealm()] );

        parent::destroy();
    }

    protected function doDestroy()
    {
        // do nothing
    }

    // Options:

    /**
     * Lifetime of the session cookie, defined in seconds.
     *
     * @param $int
     *
     * @return $this
     */
    function setLifetime($int)
    {
        // TODO
        // The effect of this function only lasts for the duration of the script.
        // Thus, you need to call session_set_cookie_params() for every request
        // and before session_start() is called.

        session_set_cookie_params($int);
        return $this;
    }


    // ..

    /**
     * Does a session exist and is it currently active?
     *
     * @throws \Exception
     */
    protected function _assertSessionRestriction()
    {
        $stat = false;
        if ( php_sapi_name() !== 'cli' ) {
            if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                $stat = ( session_status() !== PHP_SESSION_DISABLED ? true : false );
            } else {
                $stat = ( session_id() === '' ? false : true );
            }
        }

        if ( false === $stat )
            throw new \Exception('Session Cant Be Initialized.');
    }
}
