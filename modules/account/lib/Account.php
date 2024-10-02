<?php
/**
 * @author alagroy
 * @copyright 2021-2024 Laurent Jouanneau and other contributors
 * @license    MIT
 */
namespace Jelix\Authentication\Account;

use Jelix\Authentication\Core\AuthSession\UserAccountInterface;

class Account implements UserAccountInterface
{
    protected $data = array();

    /**
     * @param \jDaoRecordBase $daoRecord The account dao entry
     */
    public function __construct($daoRecord)
    {
        foreach (array_keys($daoRecord->getProperties()) as $key) {
            $this->data[$key] = $daoRecord->$key;
        }
    }

    /**
     * Gets a data from the account
     * 
     * @param string $prop The property to retrieve, if null, the method will return all Data
     * @return mixed The data you want to retrieve or null if $prop doesn't exists
     */
    public function getData($prop = null) {
        if ($prop && array_key_exists($prop, $this->data)) {
            return $this->data[$prop];
        } else if ($prop) {
            return null;
        }

        return $this->data;
    }

    public function getAccountId()
    {
        return $this->data['account_id'];
    }

    public function getRealName()
    {
        return $this->data['firstname'].' '.$this->data['lastname'];
    }

    public function getUserName()
    {
        return $this->data['username'];
    }

    public function getEmail()
    {
        return $this->data['email'];
    }
}