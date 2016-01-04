<?php
namespace Poirot\Storage\Gateway;

use Poirot\Core\BuilderSetter;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Core\Traits\EntityTrait;
use Poirot\Storage\Interfaces\iStorageData;

class BaseData extends BuilderSetter implements iStorageData
{
    use EntityTrait;

    const REALM_DEFAULT = 'poirot_default_realm';

    protected $__setup_array_priority = [
        'realm'
    ];

    /** @var string Storage Domain Realm*/
    protected $realm;


    /**
     * Construct
     *
     * @param string|iDataSetConveyor|array $realm   Realm or Setter Options
     * @param iDataSetConveyor|array        $setter  Setter Options
     */
    function __construct($realm = null, $setter = null)
    {
        if (is_string($realm))
            $this->setRealm($realm);
        else
            $setter = $realm;

        parent::__construct($setter);
    }


    // Options:

    /**
     * Set Storage Domain Realm
     *
     * @param string $realm Storage Identity
     *
     * @return $this
     */
    function setRealm($realm)
    {
        $this->realm = (string) $realm;
        return $this;
    }

    /**
     * Get Current Storage Realm Domain
     *
     * @return string
     */
    function getRealm()
    {
        if (!$this->realm)
            $this->setRealm(self::REALM_DEFAULT);

        return $this->realm;
    }

    /**
     * Set Data For This Realm
     *
     * @param $data
     *
     * @return $this
     */
    function setData($data)
    {
        $this->from($data);
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
        $this->clean();
    }



    protected function &attainDataArrayObject()
    {
        $realm = $this->getRealm();
        if (!isset($this->properties[$realm]))
            $this->properties[$realm] = [];

        return $this->properties[$realm];
    }
}
