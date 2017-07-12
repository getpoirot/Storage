<?php
namespace Poirot\Storage\Gateway;

use Poirot\Storage\Exception\Storage\exIOError;
use Poirot\Storage\Interfaces\iDataStore;
use Poirot\Std\ConfigurableSetter;


abstract class aDataStore
    extends ConfigurableSetter
    implements iDataStore
{
    /** @var string Storage Domain Realm*/
    protected $realm;
    protected $realmStorage;


    /**
     * Construct
     *
     * @param string             $realm    Realm
     * @param array|\Traversable $settings Setter Options
     *
     * @throws \Exception
     */
    function __construct($realm, $settings = null)
    {
        if ('' === $this->realm = (string) $realm)
            throw new \Exception('No Realm Is Given.');


        parent::__construct($settings);

        $this->__init();
    }

    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        // Implement
    }


    /**
     * Get Current Storage Realm Domain
     *
     * @return string
     */
    function getRealm()
    {
        return $this->realm;
    }

    /**
     * Import Data Into Current Realm
     *
     * @param array|\Traversable|null $data
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function import($data)
    {
        if ($data === null)
            return $this;

        if (!(is_array($data) || $data instanceof \Traversable || $data instanceof \stdClass))
            throw new \InvalidArgumentException(sprintf(
                'Data must be instance of \Traversable, \stdClass or array. given: (%s)'
                , \Poirot\Std\flatten($data)
            ));

        if ($data instanceof \stdClass)
            $data = \Poirot\Std\toArrayObject($data);

        
        foreach ($data as $k => $v)
            $this->set($k, $v);

        return $this;
    }

    /**
     * Is Empty?
     * @return bool
     */
    function isEmpty()
    {
        $r = true;
        foreach ($this as $_) {
            $r = false;
            break;
        }

        return $r;
    }

    /**
     * Destroy Current Realm Data Source
     *
     * @return void
     */
    function destroy()
    {
        $this->clean();

        $self = $this;
        register_shutdown_function(function() use ($self) {
            $self->doDestroy();
        });
    }


    abstract protected function doDestroy();


    // Implement Iterator

    /**
     * Retrieve an external iterator
     * @return \Traversable
     */
    abstract function getIterator();


    // Implement Countable

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    abstract function count();
}
