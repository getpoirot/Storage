<?php
namespace Poirot\Storage;

use Poirot\Core\Entity;
use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Core\Interfaces\iEntityPoirot;
use Poirot\Core\Interfaces\OptionsProviderInterface;
use Poirot\Storage\Interfaces\iStorage;

abstract class AbstractStorage
    implements iStorage,
    OptionsProviderInterface
{
    /**
     * @var Entity Meta Data
     */
    protected $meta;

    /**
     * @var StorageBaseOptions
     */
    protected $options;

    /**
     * Construct
     *
     * - Options must passed to storage,
     *   so we need to recognize identity
     *
     * @param Array|StorageBaseOptions $options
     *
     * @throws \Exception
     */
    public function __construct($options)
    {
        if ($options instanceof StorageBaseOptions)
            foreach($options->props()->writable as $opt)
                $this->options()->{$opt} = $options->{$opt};
        elseif (is_array($options))
            $this->options()->fromArray($options);
        else
            throw new \Exception(sprintf(
                'Constructor Except "Array" or Instanceof "AbstractOptions", but "%s" given.'
                , is_object($options) ? get_class($options) : gettype($options)
            ));
    }

    /**
     * Storage Options
     *
     * @return StorageBaseOptions
     */
    function options()
    {
        if (!$this->options)
            $this->options = self::optionsIns();

        return $this->options;
    }

    /**
     * Set Entity
     *
     * @param string $key   Entity Key
     * @param mixed  $value Entity Value
     *
     * @return $this
     */
    abstract function set($key, $value);

    /**
     * Get Entity Value
     *
     * @param string $key     Entity Key
     * @param null   $default Default If Not Value/Key Exists
     *
     * @return mixed
     */
    abstract function get($key, $default = null);

    /**
     * Has Entity With key?
     *
     * @param string $key Entity Key
     *
     * @return boolean
     */
    abstract function has($key);

    /**
     * Delete Entity With Key
     *
     * @param string $key Entity Key
     *
     * @return $this
     */
    abstract function del($key);

    /**
     * Get Entity Props. Keys
     *
     * @return array
     */
    abstract function keys();

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
     * @return StorageBaseOptions
     */
    static function optionsIns()
    {
        return new StorageBaseOptions();
    }

    /**
     * Set Properties
     *
     * - by deleting existence properties
     *
     * @param iEntityPoirot $entity
     *
     * @return $this
     */
    function setFrom(iEntityPoirot $entity)
    {
        foreach ($this->keys() as $key)
            // Delete All Currently Properties
            $this->del($key);

        $this->merge($entity);

        return $this;
    }

    /**
     * Merge/Set Data With Entity
     *
     * @param iEntityPoirot $entity Merge Entity
     *
     * @return $this
     */
    function merge(iEntityPoirot $entity)
    {
        foreach($entity->keys() as $key)
            $this->set($key, $entity->get($key));

        return $this;
    }

    /**
     * Get a copy of properties as hydrate structure
     *
     * @param iEntityPoirot $entity Entity
     *
     * @return mixed
     */
    function getAs(iEntityPoirot $entity)
    {
        return $entity->setFrom($this)
            ->borrow();
    }

    /**
     * Output Conveyor Props. as desired manipulated data struct.
     *
     * @return array
     */
    abstract function borrow();

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
