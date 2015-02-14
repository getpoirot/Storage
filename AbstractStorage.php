<?php
namespace Poirot\Storage;

use Poirot\Core\Entity;
use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Core\Interfaces\OptionsProviderInterface;
use Poirot\Storage\Interfaces\iStorage;

abstract class AbstractStorage extends Entity
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

        parent::__construct(); // To build default properties
        // call consIt() by entity
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
     * Output Conveyor Props. as desired manipulated data struct.
     *
     * @return array
     */
    function borrow()
    {
        return $this->getAs(new Entity($this));
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
