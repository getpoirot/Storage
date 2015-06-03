<?php
namespace Poirot\Storage;

use Poirot\Core\Entity;
use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Core\Interfaces\iPoirotEntity;
use Poirot\Core\Interfaces\OptionsProviderInterface;
use Poirot\Core\Traits\EntityTrait;
use Poirot\Storage\Interfaces\iStorageEntity;

/**
 * TODO class extend AbstractOption and data entity as object Storage::Data()->setData()|->set('dffdf', 'sdssd');
 * // it seems much more better and reliable
 * // class build with options, and data set through data gateway
 */
abstract class AbstractStorage
    implements iStorageEntity,
    OptionsProviderInterface
{
    use EntityTrait;

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

    // Implement Options Provider:

    /**
     * Storage Options
     *
     * @return StorageBaseOptions
     */
    function options()
    {
        if (!$this->options)
            $this->options = static::optionsIns();

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

    // Implement Meta Provider:

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
