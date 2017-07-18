<?php
namespace Poirot\Storage\Http;

use Poirot\Std\Struct\DataPointerArray;
use Poirot\Storage\InMemoryStore;


/**
 * note: When we set cookie variables from set_cookie
 *       Cookies will not become visible until the next
 *       loading of a page that the cookie should be visible for.
 *
 *       SO, MAKE SOME CHANGES, that variables become visible
 *       from first initialization
 */
class CookieStore
    extends InMemoryStore
{
    protected $domain    = '';
    protected $path      = '/';
    protected $secure    = false;
    protected $http_only = false;
    protected $lifetime  = /* time() + */ 2628000; // 5 years


    /**
     * Initialize
     *
     */
    protected function __init()
    {
        parent::__init();


        $this->_assertCookieRestriction();


        $realm = $this->getRealm();
        if (! isset($_COOKIE[$realm]) )
            $_COOKIE[$realm] = array();

        // PHP Allow direct change into global variable $_SESSION to manipulate data!
        $this->data = new DataPointerArray( $_COOKIE[$realm] );
    }

    /**
     * @inheritdoc
     */
    function set($key, $value)
    {
        # send cookie header
        $this->_setCookieParam($key, $value);

        # store as entity property:
        parent::set($key, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    function del($key)
    {
        if ( $this->has($key) )
            ## send cookie expire header
            $this->_setCookieParam($key, null, -2628000);


        parent::del($key);
    }

    /**
     * Destroy Current Realm Data Source
     *
     * @return void
     */
    function destroy()
    {
        /*
         * Cookies must be deleted with the same parameters as they were set with.
         * If the value argument is an empty string, or FALSE, and all other
         * arguments match a previous call to setcookie, then the cookie with the
         * specified name will be deleted from the remote client.
         * This is internally achieved by setting value to 'deleted' and expiration
         * time to one year in past.
         */
        foreach ($this as $k => $v)
            $this->_setCookieParam($k, null, -2628000);

        parent::destroy();
    }


    // Options:

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = (string) $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = (string) $path;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @param boolean $secure
     * @return $this
     */
    public function setSecure($secure)
    {
        $this->secure = (boolean) $secure;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHttpOnly()
    {
        return $this->http_only;
    }

    /**
     * @param boolean $http_only
     * @return $this
     */
    public function setHttpOnly($http_only)
    {
        $this->http_only = (boolean) $http_only;
        return $this;
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        return time() + $this->lifetime;
    }

    /**
     * @param int $lifetime
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
    }


    // ...

    protected function _setCookieParam($key, $value, $lifetime = null)
    {
        $currLifetime = $this->getLifetime();

        if ($lifetime === null)
            $lifetime = $this->getLifetime();
        else
            $this->setLifetime($lifetime);

        $key   = "{$this->getRealm()}[{$key}]";

        $r = @setcookie(
            $key
            , $value
            , $lifetime
            , $this->getPath()
            , $this->getDomain()
            , $this->getSecure()
            , $this->getHttpOnly()
        );

        if (! $r )
            throw new \Exception('Unexpected error was happen, cant set cookie.');

        $this->setLifetime($currLifetime);
    }

    /**
     * Check Cookie Protocol Restriction
     *
     * @throws \Exception
     */
    protected function _assertCookieRestriction()
    {
        $stat = false;
        if ( php_sapi_name() !== 'cli' ) {
            if ( headers_sent() )
                throw new \Exception('Headers was sent, cookies must be sent before any output from your script.');

            $stat = true;
        }

        if ( false === $stat )
            throw new \Exception('Session Cant Be Initialized.');
    }
}
