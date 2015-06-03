<?php
namespace Poirot\Storage\Adapter;

use Poirot\Core\AbstractOptions;
use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Storage\AbstractStorage;

class SessionStorage extends AbstractStorage
{
    protected $isPrepared;

    /**
     * Prepare Storage
     *
     * @throws \Exception
     * @return $this
     */
    function prepare()
    {
        if ($this->isPrepared)
            return;

        if (!$this->sessionExists())
            session_start();

        $this->isPrepared = true;
    }

    /**
     * Set Property with value
     *
     * @param string $prop  Property
     * @param mixed  $value Value
     *
     * @return $this
     */
    function set($prop, $value = '__not_set_value__')
    {
        $this->prepare();

        $ident = $this->options()->getIdent();
        $_SESSION[$ident][$prop] = $value;

        return $this;
    }

    /**
     * Get Property
     * - throw exception if property not found and default get not set
     *
     * @param string     $prop    Property name
     * @param null|mixed $default Default Value if not exists
     *
     * @throws \Exception
     * @return mixed
     */
    function get($prop, $default = '__not_set_value__')
    {
        $this->prepare();

        $val = $default;
        $ident = $this->options()->getIdent();
        if ($this->has($prop))
            $val = $_SESSION[$ident][$prop];

        return $val;
    }

    /**
     * Has Property
     *
     * @param string $prop Property
     *
     * @return boolean
     */
    function has($prop)
    {
        $this->prepare();

        $ident = $this->options()->getIdent();
        return isset($_SESSION[$ident]) && isset($_SESSION[$ident][$prop]);
    }

    /**
     * Delete a property
     *
     * @param string $prop Property
     *
     * @return $this
     */
    function del($prop)
    {
        $this->prepare();

        $ident = $this->options()->getIdent();
        if ($this->has($prop))
            unset($_SESSION[$ident][$prop]);

        return $this;
    }

    /**
     * Get Entity Props. Keys
     *
     * @return array
     */
    function keys()
    {
        $this->prepare();

        $return = [];
        $ident = $this->options()->getIdent();
        if (isset($_SESSION[$ident]))
            $return = array_keys($_SESSION[$ident]);

        return $return;
    }

    /**
     * Destroy Current Ident Entities
     *
     * @return void
     */
    function destroy()
    {
        $this->prepare();

        $ident = $this->options()->getIdent();
        unset($_SESSION[$ident]);
    }

    // In Class Usage:

    /**
     * Does a session exist and is it currently active?
     *
     * @return bool
     */
    protected function sessionExists()
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

    /**
     * Output Conveyor Props. as desired manipulated data struct.
     *
     * @return array
     */
    function borrow()
    {
        $ident = $this->options()->getIdent();

        return $_SESSION[$ident];
    }
}
