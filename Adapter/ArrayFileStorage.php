<?php
namespace Poirot\Storage\Adapter;

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
    }

    /**
     * Options Object
     *
     * @return FileStorage\Options
     */
    function options()
    {
        if (!$this->options)
            $this->options = new FileStorage\Options([
                'storage_path' => '/tmp',
                'adapter'      => $this
            ]);

        return $this->options;
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

    protected function initFile()
    {
        $file = $this->getStorageFilePath();

        if (!file_exists($file))
            $this->toFile($file, array());
    }

    function loadDataFromFile()
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
        if ($this->options()->getIdent() === null || $this->options()->getIdent() == '' )
            throw new \Exception('No Storage Identity Defined Yet!!');

        $opt_directory = $this->options()->getStoragePath();
        $file = $opt_directory . DIRECTORY_SEPARATOR . $this->options()->getIdent() . '.array.php';

        return $file;
    }
}
