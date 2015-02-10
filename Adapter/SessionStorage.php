<?php
namespace Poirot\Storage\Adapter;

use Poirot\Core\AbstractOptions;
use Poirot\Core\Entity;
use Poirot\Core\Interfaces\EntityInterface;

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
        if (!$this->sessionExists())
            session_start();

        $this->isPrepared = true;
    }

    /**
     * Is Initialized?
     *
     * @return boolean
     */
    function isPrepared()
    {
        return $this->isPrepared;
    }

    /**
     * Set Entity
     *
     * @param string $key Entity Key
     * @param mixed $value Entity Value
     *
     * @return $this
     */
    function set($key, $value)
    {
        $this->prepare();

        $ident = $this->options()->getIdent();
        $_SESSION[$ident][$key] = $value;

        return $this;
    }

    /**
     * Get Entity Value
     *
     * @param string $key Entity Key
     * @param null $default Default If Not Value/Key Exists
     *
     * @return mixed
     */
    function get($key, $default = null)
    {
        $this->prepare();

        $val = $default;
        $ident = $this->options()->getIdent();
        if ($this->has($key))
            $val = $_SESSION[$ident][$key];

        return $val;
    }

    /**
     * Has Entity With key?
     *
     * @param string $key Entity Key
     *
     * @return boolean
     */
    function has($key)
    {
        $this->prepare();

        $ident = $this->options()->getIdent();
        return isset($_SESSION[$ident]) && isset($_SESSION[$ident][$key]);
    }

    /**
     * Delete Entity With Key
     *
     * @param string $key Entity Key
     *
     * @return $this
     */
    function del($key)
    {
        $this->prepare();

        $ident = $this->options()->getIdent();
        if ($this->has($key))
            unset($_SESSION[$ident][$key]);

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

    /**
     * Get Meta Data Entity Object
     *
     * - use to access meta extra data over storage,
     *   basically used by storage decorators
     *
     * @return EntityInterface
     */
    function meta()
    {
        if (!$this->meta)
            $this->meta = new Entity();

        return $this->meta;
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
}
