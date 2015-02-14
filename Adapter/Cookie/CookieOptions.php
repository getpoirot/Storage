<?php
namespace Poirot\Storage\Adapter\Cookie;

use Poirot\Storage\StorageBaseOptions;

class CookieOptions extends StorageBaseOptions
{
    protected $domain    = '';
    protected $path      = '/';
    protected $secure    = false;
    protected $http_only = false;
    protected $lifetime  = /* time() + */ 2628000; // 5 years

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
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
     */
    public function setPath($path)
    {
        $this->path = $path;
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
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
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
     */
    public function setHttpOnly($http_only)
    {
        $this->http_only = $http_only;
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
}
 