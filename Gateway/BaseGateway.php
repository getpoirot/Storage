<?php
namespace Poirot\Storage\Gateway;

use Poirot\Core\BuilderSetter;
use Poirot\Core\Traits\EntityTrait;
use Poirot\Storage\Interfaces\iStorageGateway;

class BaseGateway extends BuilderSetter implements iStorageGateway
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
     * @param null|array $setter
     */
    function __construct($setter = null)
    {
        if (is_string($setter)) {
            $this->setRealm($setter);
            $setter = null;
        }

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

    protected function &attainDataArrayObject()
    {
        return $this->properties[$this->getRealm()];
    }
}
