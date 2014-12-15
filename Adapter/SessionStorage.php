<?php
namespace Poirot\Storage\Adapter;

use Poirot\Core\Entity;
use Poirot\Core\EntityInterface;
use Poirot\Storage\StorageInterface;

class SessionStorage implements StorageInterface
{
    /**
     * @var string Identity
     */
    protected $ident;

    /**
     * @var boolean Is Initialized?
     */
    protected $isInit = false;

    /**
     * @var Entity Meta Data
     */
    protected $meta;

    /**
     * Construct
     *
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Set Storage Identity
     *
     * @param string $identity Storage Identity
     *
     * @return $this
     */
    function setIdent($identity)
    {
        $this->ident = $identity;

        return $this;
    }

    /**
     * Get Current Storage Identity
     *
     * @return string
     */
    function getIdent()
    {
        if (!$this->ident) {
            $this->ident = session_id();
        }

        return $this->ident;
    }

    /**
     * Prepare Storage
     *
     * @throws \Exception
     * @return $this
     */
    function init()
    {
        session_start();

        if (!$this->sessionExists())
            throw new \Exception('No Session Exists.');

        $this->isInit = true;
    }

    /**
     * Does a session exist and is it currently active?
     *
     * @return bool
     */
    protected function sessionExists()
    {
        $sid = defined('SID') ? constant('SID') : false;
        if ($sid !== false && $this->getIdent())
            return true;

        if (headers_sent())
            return true;

        return false;
    }

    /**
     * Is Initialized?
     *
     * @return boolean
     */
    function isInit()
    {
        return $this->isInit;
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
        return array_keys($_SESSION);
    }

    /**
     * Destroy Current Ident Entities
     *
     * @return void
     */
    function destroy()
    {
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
}
 