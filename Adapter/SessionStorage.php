<?php
namespace Poirot\Storage\Adapter;

use Poirot\Core\Entity;
use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Storage\iStorage;

class SessionStorage extends AbstractStorage
{
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

        $_SESSION[$key] = $value;

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
        if ($this->has($key))
            $val = $_SESSION[$key];

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

        return isset($_SESSION[$key]);
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

        unset($_SESSION[$key]);

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

        return array_keys($_SESSION);
    }

    /**
     * Destroy Current Ident Entities
     *
     * @return void
     */
    function destroy()
    {
        $this->prepare();

        $_SESSION = [];
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
 