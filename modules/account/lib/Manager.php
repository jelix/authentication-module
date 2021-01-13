<?php

namespace Jelix\Authentication\Account;

use Jelix\Authentication\Core\AuthSession\AuthUser;

class Manager
{
    protected static $daoName = 'account~accounts';
    protected static $daoProfile = 'daotablesqlite';

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

        return $dao->findByName($name);
    }

    /**
     * Creates a new account
     * 
     * @param AuthUser $user The user to create
     * @param string $provider The IdentityProvider used to create the Account
     */
    public static function createAccount($user, $provider)
    {
        $name = $user->getName();
        if (self::accountExists($name)) {
            return ;
        }
        $dao = \jDao::get(self::$daoName, self::$daoProfile);
        $newAccount = \jDao::createRecord(self::$daoName, self::$daoProfile);
        $newAccount->name = $name;
        $newAccount->email = $user->getEmail();
        $newAccount->status = self::STATUS_VALID;
        $newAccount->provider = $provider;
        $dao->insert($newAccount);
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
}