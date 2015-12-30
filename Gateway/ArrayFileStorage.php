<?php
namespace Poirot\Storage\Gateway;

use Poirot\Core\AbstractOptions;
use Poirot\Core\ErrorStack;

class ArrayFileStorage extends MemoryGateway
{
    protected $isPrepared = false;
    /** @var array Loaded Data On Init */
    protected $__loadedData = null;

    // Options
    protected $dirPath;


    // Options:

    /**
     * @return string
     */
    function getDirPath()
    {
        if (!$this->dirPath)
            $this->setDirPath(sys_get_temp_dir());

        return rtrim($this->dirPath, DIRECTORY_SEPARATOR);
    }

    /**
     * Set Directory Path To Store Data Into
     *
     * @param string $dirPath
     * @return $this
     */
    function setDirPath($dirPath)
    {
        $this->dirPath = (string) $dirPath;
        return $this;
    }


    // ...

    /**
     * Empty Entity Data
     *
     * @return $this
     */
    function clean()
    {
        $file = $this->__getFilePath();
        if (file_exists($file))
            unlink($file);

        return parent::clean();
    }

    /**
     * Write Data Into Storage
     *
     * @throws \Exception
     * @return $this
     */
    function save()
    {
        $file = $this->__getFilePath();

        $data = $this->toArray();

        ErrorStack::handleError(E_ALL);
        ##  \\\\\
        $dataStr = "<?php\n". "return " . var_export($data, true) . ";\n";
        file_put_contents($file, $dataStr, LOCK_EX);
        ##  /////
        if ($exception = ErrorStack::handleDone())
            throw $exception;

        return $this;
    }


    // ...

    protected function &attainDataArrayObject()
    {
        $this->__prepare();
        return parent::attainDataArrayObject();
    }

    /**
     * Prepare Storage
     *
     * @return $this
     */
    protected function __prepare()
    {
        if ($this->isPrepared)
            return $this;

        $self = $this;
        register_shutdown_function(function() use ($self) {
            $self->__writeDown();
        });

        $this->__importData();

        $this->isPrepared = true;
        return $this;
    }

    /**
     * Write Data To File
     *
     */
    protected function __writeDown()
    {
        if (
            (!is_array($this->__loadedData) && $this->__loadedData === null) // has not any data loaded
            || @array_intersect($this->__loadedData, $this->toArray()) == $this->toArray()
        )
            // Nothing Have Changed
            return;

        $this->save();
    }

    function __importData()
    {
        $file = $this->__getFilePath();

        ErrorStack::handleError(E_ALL);
        ##  \\\\\
        $data = include $file;
        ##  /////
        if ($exception = ErrorStack::handleDone())
            throw $exception;

        $this->from($data);
        $this->__loadedData = $data;
    }

    /**
     * Get Current Storage FilePath Name
     *
     * @throws \Exception
     * @return string
     */
    protected function __getFilePath()
    {
        if ($this->getRealm() === null || $this->getRealm() == '' )
            throw new \Exception('No Storage Identity Defined Yet!!');

        $opt_directory = $this->getDirPath();
        $file = $opt_directory . DIRECTORY_SEPARATOR . $this->getRealm() . '.array.php';

        ErrorStack::handleError(E_ALL);
        ## \\\\\
        ### check for directory tree
        $dirPath = dirname($file);
        if (!file_exists($dirPath))
            mkdir($dirPath, 0777, true);
        ### check file existence
        if (!file_exists($file))
            file_put_contents($file, '');
        ##  /////
        if ($exception = ErrorStack::handleDone())
            throw $exception;

        return $file;
    }
}
