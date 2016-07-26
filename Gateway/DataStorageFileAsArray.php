<?php
namespace Poirot\Storage\Gateway;

/*
$s = new P\Storage\Gateway\DataStorageSession('my_realm');
$s->setData([
    'name'   => 'Payam',
    'family' => 'Naderi',
]);

$s->setRealm('new');
print_r(P\Std\cast($s)->toArray()); // Array ( )

$s->setRealm('my_realm');
$s->import(['email'  => 'naderi.payam@gmail.com']);
print_r(P\Std\cast($s)->toArray()); // Array ( [name] => Payam [family] => Naderi [email] => naderi.payam@gmail.com )
*/

use Poirot\Std\ErrorStack;
use Poirot\Std\Interfaces\Struct\iDataEntity;

class DataStorageFileAsArray 
    extends DataStorageMemory
{
    protected static $STATE_OK = 'OK';
    protected static $STATE_UPDATED = 'UPDATED';

    protected $isPrepared = false;

    /** @var array Loaded Data On Init */
    protected $_c_loadedDataState = array(
        // to consider what changed and data must save or not!
        // 'realm_domain' => 'UPDATED' | 'OK'
    );

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
            $this->_writeDown();
        }

        return parent::setRealm($realm);
    }

    /**
     * @override cause change state
     * @inheritdoc
     */
    function destroy()
    {
        $file = $this->_getFilePath();
        if (file_exists($file))
            unlink($file);

        $this->_changeState(self::$STATE_UPDATED);
        parent::destroy();
    }

    /**
     * @override cause change state
     * @inheritdoc
     */
    function import($data)
    {
        $this->_changeState(self::$STATE_UPDATED);
        return parent::import($data);
    }

    /**
     * @override cause change state
     * @inheritdoc
     */
    function clean()
    {
        $this->_changeState(self::$STATE_UPDATED);
        return parent::clean();
    }

    /**
     * @override cause change state
     * @inheritdoc
     */
    function del($key)
    {
        $this->_changeState(self::$STATE_UPDATED);
        return parent::del($key);
    }

    /**
     * @override cause change state
     * @inheritdoc
     */
    function set($key, $value)
    {
        $this->_changeState(self::$STATE_UPDATED);
        return parent::set($key, $value);
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

        $this->_changeState(self::$STATE_OK);
        return $this;
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
     * @return iDataEntity
     */
    protected function _newDataStorage()
    {
        $this->_prepare();

        $entity = parent::_newDataStorage();
        ## import persist data if available as defaults
        $this->_importDataInto($entity);
        return $entity;
    }

    /**
     * Prepare Storage
     *
     */
    protected function _prepare()
    {
        if ($this->isPrepared)
            return ;

        $self = $this;
        register_shutdown_function(function() use ($self) {
            $self->_writeDown();
        });

        $this->isPrepared = true;
    }

    /**
     * Import Persist Data Into Entity as Default Values
     * @param iDataEntity $entity
     * @throws \ErrorException|null
     * @throws \Exception
     */
    function _importDataInto(iDataEntity $entity)
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

        $entity->import($data);
    }

    /**
     * Write Data To File
     */
    protected function _writeDown()
    {
        if (
            !isset($this->_c_loadedDataState[$this->getRealm()])
            || $this->_c_loadedDataState[$this->getRealm()] === self::$STATE_OK
        )
            // Nothing Has Changed
            return;

        $this->save();
    }

    /**
     * Get Current Storage FilePath Name
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

    /**
     * To Consider Save State If Data Changed
     * @param string $state
     */
    protected function _changeState($state)
    {
        $realm = $this->getRealm();
        $this->_c_loadedDataState[$realm] = $state;
    }
}
