<?php
namespace Poirot\Storage\Adapter;

use Poirot\Core\Entity;
use Poirot\Core\Interfaces\EntityInterface;
use Poirot\Core\Interfaces\OptionsProviderInterface;
use Poirot\Storage\Adapter\FileStorage\Options;
use Poirot\Storage\StorageInterface;

class ArrayFileStorage extends Entity
    implements
    StorageInterface,
    OptionsProviderInterface
{
    /**
     * @var string Storage Identity
     */
    protected $ident;

    /**
     * @var boolean Is Initialized?
     */
    protected $isInit;

    /**
     * @var array Loaded Data On Init
     */
    protected $loadedCachedData;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var EntityInterface
     */
    protected $meta;

    /**
     * Construct
     *
     */
    public function __construct($ident = null)
    {
        if ($ident !== null)
            $this->setIdent($ident);

        parent::__construct();

        $this->init();
    }

    /**
     * Options Object
     *
     * @return Options
     */
    function options()
    {
        if (!$this->options)
            $this->options = new Options(['storage_path' => '/tmp']);

        return $this->options;
    }

    /**
     * Prepare Storage
     *
     * @return $this
     */
    function init()
    {
        register_shutdown_function(array($this, 'writeDown'));

        $this->isInit = true;
    }

    /**
     * Write Data To File
     *
     */
    public function writeDown()
    {
        if (
            array_intersect($this->loadedCachedData, $this->properties)
            == $this->properties
        )
            // Nothing Have Changed
            return;

        $file = $this->getStorageFilePath();
        $data = [];
        foreach($this->keys() as $key)
            // maybe need to serialize data
            $data[$key] = $this->get($key);

        $this->toFile($file, $data);
    }

    protected function toFile($filename, array $data)
    {
        $dataStr = "<?php\n". "return " . var_export($data, true) . ";\n";

        set_error_handler(
            function ($error, $message = '', $file = '', $line = 0) use ($filename) {
                throw new \RuntimeException(sprintf(
                    'Error writing to "%s": %s',
                    $filename, $message
                ), $error);
            }, E_WARNING
        );

        try {
            file_put_contents($filename, $dataStr, LOCK_EX);
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }

        restore_error_handler();
    }

    /**
     * Set Storage Identity
     *
     * @param string $identity Storage Identity
     *
     * @return $this
     */
    function setIdent($identity)
    {
        if ($this->ident != null && $this->ident == $identity)
            // Nothing Changed
            return $this;

        if ($this->ident != null)
            // If Ident Change Write Down Current Data
            $this->writeDown();

        $this->ident = $identity;
        $this->loadDataFromFile();

        return $this;
    }

    /**
     * Get Current Storage Identity
     *
     * @return string
     */
    function getIdent()
    {
        return $this->ident;
    }

    /**
     * Is Initialized?
     *
     * @return boolean
     */
    function isInit()
    {
        return $this->isInit;
    }

    /**
     * Destroy Current Ident Entities
     *
     * @return void
     */
    function destroy()
    {
        $file = $this->getStorageFilePath();
        if (file_exists($file))
            unlink($file);

        foreach($this->keys() as $key)
            $this->del($key);
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

    protected function initFile()
    {
        $file = $this->getStorageFilePath();

        if (!file_exists($file))
            $this->toFile($file, array());
    }

    protected function loadDataFromFile()
    {
        $this->initFile();

        $file = $this->getStorageFilePath();
        $data = include $file;
        foreach($data as $key => $val)
            $this->set($key, $val);

        $this->loadedCachedData = $this->properties;
    }

    /**
     * Get Current Storage FilePath Name
     *
     * @throws \Exception
     * @return string
     */
    protected function getStorageFilePath()
    {
        if ($this->getIdent() === null || $this->getIdent() == '' )
            throw new \Exception('No Storage Identity Defined Yet!!');

        $opt_directory = $this->options()->getStoragePath();
        $file = $opt_directory . DIRECTORY_SEPARATOR . $this->getIdent() . '.array.php';

        return $file;
    }
}
