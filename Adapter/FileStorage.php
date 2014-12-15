<?php
namespace Poirot\Storage\Adapter;

use Poirot\Core\Entity;
use Poirot\Core\EntityInterface;
use Poirot\Storage\StorageInterface;

class FileStorage extends Entity
    implements StorageInterface
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
     * Prepare Storage
     *
     * @return $this
     */
    function init()
    {
        $this->loadDataFromFile();

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
        if ($this->ident !== null
            && ($this->ident != $identity)
        )
            // If Ident Change Write Down Current Data
            $this->writeDown();

        $this->ident = $identity;

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
        // TODO: Implement meta() method.
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

        $opt_directory = APP_DIR_APPLICATION;
        $file = $opt_directory . DIRECTORY_SEPARATOR . $this->getIdent() . '.array.php';

        return $file;
    }
}
