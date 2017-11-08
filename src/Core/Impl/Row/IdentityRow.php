<?php
/**
 * Created by PhpStorm.
 * User: ducanhle
 * Date: 11/8/17
 * Time: 10:03 AM
 */

namespace CreativeDelta\User\Core\Impl\Row;


use CreativeDelta\User\Core\Impl\Table\UserIdentityTable;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\RowGateway\RowGatewayInterface;

class IdentityRow implements RowGatewayInterface
{
    /**
     * @var UserIdentityTable
     */
    protected $identityTable;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $autoSequence;

    /**
     * @var bool
     */
    protected $rowExistInDatabase;

    /**
     * IdentityRow constructor.
     * @param UserIdentityTable $identityTable
     * @param array             $data
     * @param string            $seq
     */
    public function __construct(UserIdentityTable $identityTable, array $data = null, $seq = null)
    {
        $this->identityTable      = $identityTable;
        $this->data               = $data;
        $this->autoSequence       = $seq;
        $this->rowExistInDatabase = false;
    }

    public function exchangeArray(array $data)
    {
        $this->setId($data[UserIdentityTable::ID_NAME]);
        $this->setAccount($data[UserIdentityTable::COLUMN_ACCOUNT]);
        $this->setSecret($data[UserIdentityTable::COLUMN_PASSWORD]);
        $this->setState($data[UserIdentityTable::COLUMN_STATE]);
    }

    public function populate(array $data, $exist = false)
    {
        $this->exchangeArray($data);
        $this->rowExistInDatabase = $exist;
    }

    /**
     * @return string
     */
    public function getAutoSequence()
    {
        return $this->autoSequence;
    }

    /**
     * @param string $autoSequence
     */
    public function setAutoSequence($autoSequence)
    {
        $this->autoSequence = $autoSequence;
    }

    public function getId()
    {
        return $this->data[UserIdentityTable::ID_NAME];
    }

    public function setId($id)
    {
        $this->data[UserIdentityTable::ID_NAME] = $id;
    }

    public function getAccount()
    {
        return $this->data[UserIdentityTable::COLUMN_ACCOUNT];
    }

    public function setAccount($account)
    {
        $this->data[UserIdentityTable::COLUMN_ACCOUNT] = $account;
    }

    public function setSecret($secret)
    {
        return $this->data[UserIdentityTable::COLUMN_PASSWORD] = $secret;
    }

    public function getSecret()
    {
        $this->data[UserIdentityTable::COLUMN_PASSWORD];
    }

    public function getState()
    {
        return $this->data[UserIdentityTable::COLUMN_STATE];
    }

    public function setState($state)
    {
        $this->data[UserIdentityTable::COLUMN_STATE] = $state;
    }

    public function save()
    {
        if ($this->rowExistInDatabase) {

            $this->identityTable->getTableGateway()->update($this->data, [UserIdentityTable::ID_NAME => $this->getId()]);

        } else {

            $count = $this->identityTable->getTableGateway()->insert($this->data);

            if ($count > 0) {

                $driver = $this->identityTable->getTableGateway()->getAdapter()->getDriver();
                $newId  = $driver instanceof Pdo ? $driver->getLastGeneratedValue($this->getAutoSequence()) : $driver->getLastGeneratedValue();

                $this->rowExistInDatabase = true;
                $this->setId($newId);
            }
        }
    }

    public function delete()
    {
        $this->identityTable->getTableGateway()->delete([
            UserIdentityTable::ID_NAME => $this->getId()
        ]);
    }

}