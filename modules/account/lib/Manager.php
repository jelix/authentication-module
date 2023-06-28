<?php

namespace Jelix\Authentication\Account;

use jAuthentication;
use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\IdentityProviderInterface;

class Manager
{
    protected static $daoName = 'account~accounts';
    
    protected static $daoProfile = 'account';

    const STATUS_VALID = 1;
    const STATUS_INVALID = 0;
    const STATUS_DISABLED = -1;
    const STATUS_DELETED = -2;

    /**
     * Retrieves a registered account from the Dao.
     * 
     * @param string $username The account's username
     * 
     * @return Account An Account object containing the account's infos or null if the account doesn't exist.
     */
    public static function getAccount($username)
    {
        $dao = \jDao::get(self::$daoName, self::$daoProfile);

        $record = $dao->findByUserName($username);
        if (!$record) {
            return null;
        }
        return new Account($record);
    }

    /**
     * Creates an account object
     *
     * @return \jDaoRecordBase The record containing the account
     */
    public static function createAccountObject($userName, $email)
    {
        $newAccount = \jDao::createRecord(self::$daoName, self::$daoProfile);
        $newAccount->username = $userName;
        $newAccount->email = $email;
        $newAccount->status = self::STATUS_VALID;
        return $newAccount;
    }

    public static function saveNewAccount($newAccount)
    {
        $dao = \jDao::get(self::$daoName, self::$daoProfile);
        $dao->insert($newAccount);
    }


    /**
     * Creates a new account from the authenticated user
     * 
     * @param AuthUser $user The user to create
     * @param IdentityProviderInterface $provider The IdentityProvider used to create the Account
     * 
     * @return \jDaoRecordBase The record containing the new account or null if account already exists.
     */
    public static function createAccountFromAuthUser($user, $provider, $status = null)
    {
        $name = $user->getLogin();
        if (self::accountExists($name)) {
            return null;
        }

        if ($status === null) {
            $status = self::STATUS_VALID;
        }

        $dao = \jDao::get(self::$daoName, self::$daoProfile);
        $newAccount = \jDao::createRecord(self::$daoName, self::$daoProfile);
        $newAccount->name = $name;
        $newAccount->email = $user->getEmail();
        $newAccount->status = $status;
        $dao->insert($newAccount);
        return $newAccount;
    }

    /**
     * Checks whether an account exists for the given name
     * 
     * @param string $username The name to check
     * @return bool true if account exists
     */
    public static function accountExists($username)
    {
        if (self::getAccount($username)) {
            return true;
        }
        return false;
    }

    public static function getCurrentUserAccount()
    {
        $user = jAuthentication::getCurrentUser();
        
        if (!$user) {
            return null;
        }

        return self::getAccount($user->getName());
    }

    /**
     * Modifies the information of a user
     * 
     * @param array $newInfos An associative array containing the information to modify and their new values
     * @param Account $user the user to modify, if null, the modified user will be the current one.
     * 
     * @return bool False if an error occurred
     */
    public static function modifyInfos($newInfos, $user = null)
    {
        if ($user == null) {
            $user = self::getCurrentUserAccount();
            if (!$user) {
                return false;
            }
        }

        $dao = \jDao::get(self::$daoName, self::$daoProfile);
        $record = $dao->get($user->getData('account_id'));
        foreach ($newInfos as $prop => $value) {
            $record->$prop = $value;
        }

        $dao->update($record);
        return true;
    }

    public static function getAccountList()
    {
        return \jDao::get(self::$daoName, self::$daoProfile)->findAll();
    }
}