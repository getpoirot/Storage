<?php
namespace Poirot\Storage\Gateway;

use Poirot\Core\AbstractOptions;

class SessionData extends BaseData
{
    /** @var boolean */
    protected $isPrepared = false;

    /**
     * Destroy Current Realm Data Source
     *
     * @return void
     */
    function destroy()
    {
        $data = &$this->attainDataArrayObject();
        unset($data);
        parent::destroy();
    }

    // ...

    /**
     * Prepare Storage
     *
     * @throws \Exception
     * @return $this
     */
    protected function __prepare()
    {
        if ($this->isPrepared)
            return;

        if (!$this->__checkSessionRestriction())
            session_start();

        $this->isPrepared = true;
    }

    /**
     * Does a session exist and is it currently active?
     *
     * @return bool
     */
    protected function __checkSessionRestriction()
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

    protected function &attainDataArrayObject()
    {
        $this->__prepare();
        $realm = $this->getRealm();
        if (!isset($_SESSION[$realm]))
            $_SESSION[$realm] = [];

        return $_SESSION[$realm];
    }
}
