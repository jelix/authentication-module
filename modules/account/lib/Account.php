<?php

namespace Jelix\Authentication\Account;

class Account
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
}