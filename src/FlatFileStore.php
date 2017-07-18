<?php
namespace Poirot\Storage;

use Poirot\Std\ErrorStack;
use Poirot\Std\Exceptions\exImmutable;
use Poirot\Storage\Exception\exDataMalformed;
use Poirot\Storage\Exception\Storage\exReadError;
use Poirot\Storage\Interchange\SerializeInterchange;
use Poirot\Storage\Interfaces\iInterchangeable;


class FlatFileStore
    extends InMemoryStore
{
    protected static $STATE_OK      = 'OK';
    protected static $STATE_UPDATED = 'UPDATED';


    protected $isPrepared = false;

    /** @var array Loaded Data On Init */
    protected $_states = [
        // to consider what changed and data must save or not!
        // 'realm_domain' => 'UPDATED' | 'OK'
    ];


    // Options:
    protected $dirPath;
    protected $interchange;


    /**
     * Initialize
     *
     */
    protected function __init()
    {
        parent::__init();


        # Register Write Shutdown Function
        #
        $self = $this;
        register_shutdown_function(function() use ($self) {
            $self->_writeDataOnShutdown();
        });

        # load persist data if available as defaults
        #
        $this->_load();
    }

    /**
     * @override cause change state
     * @inheritdoc
     */
    function set($key, $value)
    {
        $this->_setState(self::$STATE_UPDATED);
        return parent::set($key, $value);
    }

    /**
     * @override cause change state
     * @inheritdoc
     */
    function del($key)
    {
        $this->_setState(self::$STATE_UPDATED);
        parent::del($key);
    }

    /**
     * @override cause change state
     * @inheritdoc
     */
    function clean()
    {
        $this->_setState(self::$STATE_UPDATED);
        parent::clean();
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

        $data = $this->data;
        $data = $this->getDataInterchange()->makeForward($data);
        $dataStr = "<?php\n". "return " . var_export($data, true) . ";\n";

        ErrorStack::handleError(E_ALL);
        ##  \\\\\
        ### check for directory tree
        $dirPath = dirname($file);
        if (! file_exists($dirPath) )
            if ( false === mkdir($dirPath, 0777, true) )
                throw new \RuntimeException(sprintf('Cant create store directory on (%s).', $dirPath));

        file_put_contents($file, $dataStr, LOCK_EX);
        ##  /////
        if ($exception = ErrorStack::handleDone())
            throw $exception;

        $this->_setState(self::$STATE_OK);
        return $this;
    }

    protected function doDestroy()
    {
        parent::doDestroy();

        $file = $this->_getFilePath();
        if ( file_exists($file) )
            unlink($file);

        $this->_setState(self::$STATE_OK);
    }

    // Options:

    /**
     * @return string
     */
    function getDirPath()
    {
        if (! $this->dirPath )
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

    function giveDataInterchange(iInterchangeable $interchange)
    {
        if ($this->interchange)
            throw new exImmutable('Data Interchange is Immutable and given before.');

        $this->interchange = $interchange;
        return $this;
    }

    /**
     * @return iInterchangeable
     */
    function getDataInterchange()
    {
        if (! $this->interchange )
            $this->giveDataInterchange(new SerializeInterchange);

        return $this->interchange;
    }

    // ..

    /**
     * Import Persist Data Into Entity as Default Values
     *
     * ! Load Entire File Into Memory
     *
     * @throws \Exception
     */
    function _load()
    {
        $file = $this->_getFilePath();
        if (! file_exists($file) )
            ## nothing to import, no data written yet!
            return;

        ErrorStack::handleError(E_ALL);
        ##  \\\\\
        $data = include $file;
        $data = $this->getDataInterchange()->retrieveBackward($data);
        ##  /////
        if ( $exception = ErrorStack::handleDone() )
            throw new exReadError('Error While Read Data.', $exception->getCode(), $exception);


        if (! is_array($data) )
            throw new exDataMalformed('Error Data Structure, it must be array.');

        $this->data = $data;
    }

    /**
     * To Consider Save State If Data Changed
     * @param string $state
     */
    protected function _setState($state)
    {
        $realm = $this->getRealm();
        $this->_states[$realm] = $state;
    }

    /**
     * Write Data To File
     */
    protected function _writeDataOnShutdown()
    {
        if (
            !isset( $this->_states[$this->getRealm()] )
            || $this->_states[$this->getRealm()] === self::$STATE_OK
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
        $opt_directory = $this->getDirPath();
        $file = $opt_directory . DIRECTORY_SEPARATOR . $this->getRealm() . '.array.php';

        return $file;
    }
}
