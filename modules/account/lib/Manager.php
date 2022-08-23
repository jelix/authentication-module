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
     * @param string $login The account's login
     * 
     * @return Account An Account object containing the account's infos or null if the account doesn't exist.
     */
    public static function getAccount($name)
    {
        $dao = \jDao::get(self::$daoName, self::$daoProfile);

        $record = $dao->findByName($name);
        if (!$record) {
            return null;
        }
        return new Account($record);
    }

    /**
     * Creates a new account
     * 
     * @param AuthUser $user The user to create
     * @param IdentityProviderInterface $provider The IdentityProvider used to create the Account
     * 
     * @return \jDaoRecordBase The record containing the new account or null if account already exists.
     */
    public static function createAccount($user, $provider)
    {
        $name = $user->getName();
        if (self::accountExists($name)) {
            return null;
        }
        $dao = \jDao::get(self::$daoName, self::$daoProfile);
        $newAccount = \jDao::createRecord(self::$daoName, self::$daoProfile);
        $newAccount->name = $name;
        $newAccount->email = $user->getEmail();
        $newAccount->status = self::STATUS_VALID;
        $newAccount->provider = $provider->getId();
        $dao->insert($newAccount);
        return $newAccount;
    }

    /**
     * Checks wether an account exists for the given name
     * 
     * @param string $name The name to check
     * @return bool true if account exists
     */
    public static function accountExists($name)
    {
        if (self::getAccount($name)) {
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
     * Modifies the informations of an user
     * 
     * @param array $newInfos An associative array containing the information to modify and their new values
     * @param Account $user the user to modify, if null, the modified user will be the current one.
     * 
     * @return bool False if an error occured
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
}