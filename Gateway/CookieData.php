<?php
namespace Poirot\Storage\Gateway;

/**
 * note: When we set cookie variables from set_cookie
 *       Cookies will not become visible until the next
 *       loading of a page that the cookie should be visible for.
 *
 *       SO WE MAKE SOME CHANGES, that variables become visible
 *       from first initialization
 */
class CookieData extends BaseData
{
    protected $domain    = '';
    protected $path      = '/';
    protected $secure    = false;
    protected $http_only = false;
    protected $lifetime  = /* time() + */ 2628000; // 5 years


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
        $this->domain = $domain;
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
        # send cookie header
        $key   = "{$this->getRealm()}[{$prop}]";
        $this->__setCookieParam($key, $value);

        # store as entity property:
        parent::set($prop, $value);

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
         $value = parent::get($prop, $default);
         $value = unserialize($value);

         return $value;
     }

    /**
     * Empty Entity Data
     *
     * @return $this
     */
    function clean()
    {
        /*
         * Cookies must be deleted with the same parameters as they were set with.
         * If the value argument is an empty string, or FALSE, and all other
         * arguments match a previous call to setcookie, then the cookie with the
         * specified name will be deleted from the remote client.
         * This is internally achieved by setting value to 'deleted' and expiration
         * time to one year in past.
         */
        $this->__setCookieParam($this->getRealm(), null, -2628000);

        return parent::clean();
    }

    /**
     * Delete a property
     *
     * @param string $prop Property
     *
     * @return $this
     */
    public function del($prop)
    {
        if ($this->has($prop)) {
            ## send cookie expire header
            $key   = "{$this->getRealm()}[{$prop}]";
            $this->__setCookieParam($key, null, -2628000);
        }

        parent::del($prop);
        return $this;
    }


    // ...

    protected function __setCookieParam($key, $value, $lifetime = null)
    {
        $this->__checkCookieRestriction();

        if (is_bool($value))
            $value = (boolean) $value;
        elseif ($value !== null)
            $value = serialize($value);

        $currLifetime = $this->getLifetime();

        if ($lifetime === null)
            $lifetime = $this->getLifetime();
        else
            $this->setLifetime($lifetime);

        $r = @setcookie(
            $key
            , $value
            , $lifetime
            , $this->getPath()
            , $this->getDomain()
            , $this->getSecure()
            , $this->getHttpOnly()
        );

        if (!$r)
            throw new \Exception(
                'Unexpected error was happen, cant set cookie.'
            );

        $this->setLifetime($currLifetime);
    }

    /**
     * Check Cookie Protocol Restriction
     *
     * @throws \Exception
     */
    protected function __checkCookieRestriction()
    {
        if (headers_sent())
            throw new \Exception(
                'Headers was sent, cookies must be sent before any output from your script.'
            );
    }

    protected function &attainDataArrayObject()
    {
        return $_COOKIE[$this->getRealm()];
    }
}
