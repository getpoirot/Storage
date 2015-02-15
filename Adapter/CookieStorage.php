<?php
namespace Poirot\Storage\Adapter;

use Poirot\Core\Traits\EntityTrait;
use Poirot\Storage\AbstractStorage;

/**
 * note: When we set cookie variables from set_cookie
 *       Cookies will not become visible until the next
 *       loading of a page that the cookie should be visible for.
 *
 *       SO WE MAKE SOME CHANGES, that variables become visible
 *       from become burn
 */
class CookieStorage extends AbstractStorage
{
    use EntityTrait {
        set    as protected _t__set;
        get    as protected _t__get;
        del    as protected _t__del;
        has    as protected _t__has;
        borrow as protected _t_borrow;
    }

    /**
     * @var Cookie\CookieOptions
     */
    protected $options;

    /**
     * note: Avoid Trait Construct Collide
     *
     * Construct
     *
     * - Options must passed to storage,
     *   so we need to recognize identity
     *
     * @param Array|Cookie\CookieOptions $options
     *
     * @throws \Exception
     */
    function __construct($options)
    {
        parent::__construct($options);
    }

    /**
     * Get An Bare Options Instance
     *
     * ! it used on easy access to options instance
     *   before constructing class
     *   [php]
     *      $opt = Filesystem::optionsIns();
     *      $opt->setSomeOption('value');
     *
     *      $class = new Filesystem($opt);
     *   [/php]
     *
     * @return Cookie\CookieOptions
     */
    static function optionsIns()
    {
        return new Cookie\CookieOptions();
    }

    /**
     * Set Entity
     *
     * @param string $key Entity Key
     * @param mixed $value Entity Value
     *
     * @throws \Exception
     * @return $this
     */
    function set($key, $value)
    {
        $this->checkRestriction();

        // store as entity property:
        $this->_t__set($key, $value);

        // store in cookie:
        $ident = $this->options()->getIdent();
        $key   = "{$ident}[{$key}]";

        $this->setCookie($key, $value);

        return $this;
    }

        /**
         * Check Cookie Protocol Restriction
         *
         * @throws \Exception
         */
        protected function checkRestriction()
        {
            if (headers_sent())
                throw new \Exception(
                    'Headers was sent, cookies must be sent before any output from your script.'
                );
        }

    /**
     * Get Entity Value
     *
     * @param string $key     Entity Key
     * @param null   $default Default If Not Value/Key Exists
     *
     * @return mixed
     */
     function get($key, $default = null)
     {
         if ($this->_t__has($key)) {
             $return = $this->_t__get($key, $default);
         } else {
             $ident = $this->options()->getIdent();
             if (isset($_COOKIE[$ident]) && isset($_COOKIE[$ident][$key]))
                 // get key value from cookie
                 $return = unserialize($_COOKIE[$ident][$key]);
         }

         $return = (isset($return)) ? $return : $default;

         return $return;
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
        return array_key_exists($key, $this->borrow());
    }

    /**
     * Destroy Current Ident Entities
     *
     * - Ident relies on the options
     *
     * @throws \Exception
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
        foreach($this->keys() as $prop)
            $this->del($prop);

        $this->setCookie($this->options()->getIdent(), null, -2628000);

        // Delete for current request:

        $ident = $this->options()->getIdent();
        unset($_COOKIE[$ident]);

        $this->properties = [];
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
        $this->_t__del($prop);

        $ident = $this->options()->getIdent();
        if (isset($_COOKIE[$ident]) && isset($_COOKIE[$ident][$prop])) {
            $key   = "{$ident}[{$prop}]";
            $this->setCookie($key, null, -2628000);

            unset($_COOKIE[$ident][$prop]);
        }

        return $this;
    }

    /**
     * Get All Properties Keys
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->borrow());
    }

    /**
     * Output Conveyor Props. as desired manipulated data struct.
     *
     * ! Be Aware of the situation for classes that extend Entity
     *   and maybe have stored original properties in the other way
     *   instead of $this->properties in exp. for session storage,
     *   so i prefer use:
     *   [code]
     *      return $this->getAs(new Entity($this));
     *   [/code]
     *
     * @return mixed
     */
    public function borrow()
    {
        $cArray = [];
        $ident = $this->options()->getIdent();
        if (isset($_COOKIE[$ident]))
            foreach(array_keys($_COOKIE[$ident]) as $prop)
                $cArray[$prop] = $this->get($prop);

        $cArray = array_merge(
            $cArray
            , $this->_t_borrow()
        );

        return $cArray;
    }

    protected function setCookie($key, $value, $lifetime = null)
    {
        if (is_bool($value))
            $value = (boolean) $value;
        elseif ($value !== null)
            $value = serialize($value);

        $currLifetime = $this->options()->getLifetime();

        if ($lifetime === null)
            $lifetime = $this->options()->getLifetime();
        else
            $this->options()->setLifetime($lifetime);

        $r = setcookie(
            $key
            , $value
            , $lifetime
            , $this->options()->getPath()
            , $this->options()->getDomain()
            , $this->options()->getSecure()
            , $this->options()->getHttpOnly()
        );

        if (!$r)
            throw new \Exception(
                'Unexpected error was happen, cant set cookie.'
            );

        $this->options()->setLifetime($currLifetime);
    }
}
 