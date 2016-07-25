<?php
namespace Poirot\Storage\Gateway;

/*
new DataStorageFileAsArray(['dir_path' => PR_DIR_TEMP, 'realm' => 'user_data']);
*/

use Poirot\Std\ErrorStack;
use Poirot\Std\Interfaces\Struct\iDataEntity;

class DataStorageFileAsArray 
    extends DataStorageMemory
{
    protected $isPrepared = false;

    /** @var array Loaded Data On Init */
    protected $_c_loadedDataState = null;

    // Options
    protected $dirPath;


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
        if($this->getRealm() !== null && $realm !== $this->getRealm()) {
            ## save current state, prepare new realm
            $this->save();
        }

        return parent::setRealm($realm);
    }

    // ...

    /**
     * Destroy Current Realm Data Source
     *
     * @return void
     */
    function destroy()
    {
        $file = $this->_getFilePath();
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
        $file = $this->_getFilePath();

        $data = \Poirot\Std\cast($this)->toArray();

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

    /**
     * @return iDataEntity
     */
    protected function _newDataStorage()
    {
        $this->_prepare();
        return parent::_newDataStorage();
    }
    
    
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

    
    // ..

    /**
     * Prepare Storage
     *
     * @return $this
     */
    protected function _prepare()
    {
        if ($this->isPrepared)
            return $this;

        $self = $this;
        register_shutdown_function(function() use ($self) {
            $self->_writeDown();
        });

        $this->isPrepared = true;

        // ...

        ## import data need attain object and its prepare object again
        ## move after prepared flag to avoid callback recursion
        $this->_importData();
        return $this;
    }

    /**
     * Write Data To File
     *
     */
    protected function _writeDown()
    {
        $thisAsArray = \Poirot\Std\cast($this)->toArray();

        if (
            @array_intersect_assoc($this->_c_loadedDataState, $thisAsArray) == $thisAsArray
        )
            // Nothing Have Changed
            return;

        $this->save();
    }

    function _importData()
    {
        $file = $this->_getFilePath();
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

        $this->import($data);
        $this->_c_loadedDataState = $data;
    }

    /**
     * Get Current Storage FilePath Name
     *
     * @throws \Exception
     * @return string
     */
    protected function _getFilePath()
    {
        if ($this->getRealm() === null || $this->getRealm() == '' )
            throw new \Exception('No Storage Identity Defined Yet!!');

        $opt_directory = $this->getDirPath();
        $file = $opt_directory . DIRECTORY_SEPARATOR . $this->getRealm() . '.array.php';

        return $file;
    }
}
