<?php
namespace Poirot\Storage\Adapter;

use Poirot\Core\AbstractOptions;

class ArrayFileStorage extends AbstractStorage
{
    /**
     * @var array Loaded Data On Init
     */
    protected $loadedCachedData = null;

    /**
     * @var FileStorage\Options
     */
    protected $options;

    /**
     * Constructor Init
     */
    function consIt()
    {
        $this->prepare();

        $this->options()->setAdapter($this);
    }

    /**
     * Prepare Storage
     *
     * @return $this
     */
    function prepare()
    {
        if ($this->isPrepared())
            return $this;

        register_shutdown_function(array($this, 'writeDown'));
        $this->loadDataFromFile();

        $this->isPrepared = true;

        return $this;
    }

    /**
     * Is Initialized?
     *
     * @return boolean
     */
    function isPrepared()
    {
        return $this->isPrepared;
    }

    /**
     * Write Data To File
     *
     */
    public function writeDown()
    {
        if (
            (!is_array($this->loadedCachedData) && $this->loadedCachedData === null) // has not any data loaded
            || @array_intersect($this->loadedCachedData, $this->properties) == $this->properties
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
        set_error_handler(
            function ($error, $message = '', $file = '', $line = 0) use ($filename) {
                throw new \RuntimeException(sprintf(
                    'Error writing to "%s": %s',
                    $filename, $message
                ), $error);
            }, E_WARNING
        );

        // check for directory tree
        $filedir = dirname($filename);
        if (!file_exists($filedir))
            mkdir($filedir, 0777, true);

        try {
            $dataStr = "<?php\n". "return " . var_export($data, true) . ";\n";
            file_put_contents($filename, $dataStr, LOCK_EX);
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }

        restore_error_handler();
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

    function loadDataFromFile()
    {
        $this->initFile();

        $file = $this->getStorageFilePath();

        set_error_handler(
            function ($error, $message = '', $file = '', $line = 0) use ($file) {
                throw new \RuntimeException(sprintf(
                    'Error Reading File "%s": %s',
                    $file, $message
                ), $error);
            }, E_ALL
        );

        try {
            $data = include $file;
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }
        restore_error_handler();

        foreach($data as $key => $val)
            $this->set($key, $val);

        $this->loadedCachedData = $this->properties;
    }

    protected function initFile()
    {
        $file = $this->getStorageFilePath();

        if (!file_exists($file))
            $this->toFile($file, array());
    }

    /**
     * Get Current Storage FilePath Name
     *
     * @throws \Exception
     * @return string
     */
    protected function getStorageFilePath()
    {
        if ($this->options()->getIdent() === null || $this->options()->getIdent() == '' )
            throw new \Exception('No Storage Identity Defined Yet!!');

        $opt_directory = $this->options()->getStoragePath();
        $file = $opt_directory . DIRECTORY_SEPARATOR . $this->options()->getIdent() . '.array.php';

        return $file;
    }

    /**
     * Options Object
     *
     * @return FileStorage\Options
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
     * @return FileStorage\Options
     */
    static function optionsIns()
    {
        return new FileStorage\Options([
            'storage_path' => '/tmp',
        ]);
    }
}
