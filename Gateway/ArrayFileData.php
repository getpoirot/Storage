<?php
namespace Poirot\Storage\Gateway;

use Poirot\Core\AbstractOptions;
use Poirot\Core\ErrorStack;

class ArrayFileData extends MemoryData
{
    protected $isPrepared = false;
    /** @var array Loaded Data On Init */
    protected $__loadedDataState = null;

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

    /**
     * Set Storage Domain Realm
     *
     * @param string $realm Storage Identity
     *
     * @return $this
     */
    function setRealm($realm)
    {
        $realm = (string) $realm;
        if($this->realm !== null && $realm !== $this->realm) {
            ## save current state, prepare new realm
            $this->save();
            $this->__importData();

            $dataSource = &$this->attainDataArrayObject();
            $dataSource = [];
        }

        $this->realm = $realm;
        return $this;
    }

    // ...

    /**
     * Destroy Current Realm Data Source
     *
     * @return void
     */
    function destroy()
    {
        $file = $this->__getFilePath();
        if (file_exists($file))
            unlink($file);

        parent::destroy();
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
        ### check for directory tree
        $dirPath = dirname($file);
        if (!file_exists($dirPath))
            mkdir($dirPath, 0777, true);

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

        $this->isPrepared = true;

        // ...

        ## import data need attain object and its prepare object again
        ## move after prepared flag to avoid callback recursion
        $this->__importData();
        return $this;
    }

    /**
     * Write Data To File
     *
     */
    protected function __writeDown()
    {
        if (
            @array_intersect_assoc($this->__loadedDataState, $this->toArray()) == $this->toArray()
        )
            // Nothing Have Changed
            return;

        $this->save();
    }

    function __importData()
    {
        $file = $this->__getFilePath();
        if (!file_exists($file))
            ## nothing to import, no data written yet!
            return;

        ErrorStack::handleError(E_ALL);
        ##  \\\\\
        $data = include $file;
        if (!is_array($data))
            throw new \Exception('Error Data Structure, it must be array.');
        ##  /////
        if ($exception = ErrorStack::handleDone())
            throw $exception;

        $this->from($data);
        $this->__loadedDataState = $data;
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

        return $file;
    }
}
