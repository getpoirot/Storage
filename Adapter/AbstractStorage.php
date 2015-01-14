<?php
namespace Poirot\Storage\Adapter;

use Poirot\Core\Entity;
use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Core\Interfaces\OptionsProviderInterface;
use Poirot\Storage\Interfaces\iStorage;

abstract class AbstractStorage extends Entity
    implements iStorage,
    OptionsProviderInterface
{
    /**
     * @var boolean Is Prepared?
     */
    protected $isPrepared = false;

    /**
     * @var Entity Meta Data
     */
    protected $meta;

    /**
     * @var AbstractOptions
     */
    protected $options;

    /**
     * Construct
     *
     * - Options must passed to storage,
     *   so we need to recognize identity
     *
     * @param Array|AbstractOptions $options
     *
     * @throws \Exception
     */
    public function __construct($options)
    {
        if ($options instanceof AbstractOptions)
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
     * @return AbstractOptions
     */
    function options()
    {
        if (!$this->options)
            $this->options = new AbstractOptions();

        return $this->options;
    }

    /**
     * Prepare Storage
     *
     * @return $this
     */
    abstract function prepare();

    /**
     * Is Initialized?
     *
     * @return boolean
     */
    abstract function isPrepared();

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
