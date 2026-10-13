<?php
/**
 * @author alagroy
 * @copyright 2021-2026 Laurent Jouanneau and other contributors
 * @license    MIT
 */
namespace Jelix\Authentication\Account;

use jAuthentication;
use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\IdentityProviderInterface;

class Manager
{
    protected static $daoName = 'account~accounts';

    protected static $daoIdp = 'account~account_idp';

    protected $daoProfile;

    /**
     * @var ClaimMapping
     */
    protected $claimMapping;

    const STATUS_VALID = 1;
    const STATUS_INVALID = 0;
    const STATUS_DISABLED = -1;
    const STATUS_DELETED = -2;

    public function __construct($daoProfile = 'account', ClaimMapping $claimMapping = null)
    {
        $this->daoProfile = $daoProfile;

        if ($claimMapping === null) {
            $configSection = 'account:claim-mapping';
            $configData = [];
            if (property_exists(\jApp::config(), $configSection) &&
                is_array(\jApp::config()->$configSection)
            ) {
                $configData = \jApp::config()->$configSection;
            }
            $claimMapping = new ClaimMapping($configData);
        }
        $this->claimMapping = $claimMapping;
    }

    /**
     * Retrieves a registered account from the Dao.
     *
     * @param string $username The account's username
     *
     * @return Account|null An Account object containing the account's infos or null if the account doesn't exist.
     */
    public function getAccount($username)
    {
        $dao = \jDao::get(self::$daoName, $this->daoProfile);

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
    public function createAccountObject($userName, $email)
    {
        $newAccount = \jDao::createRecord(self::$daoName, $this->daoProfile);
        $newAccount->username = $userName;
        $newAccount->email = $email;
        $newAccount->status = self::STATUS_VALID;
        return $newAccount;
    }

    public function saveNewAccount($newAccount)
    {
        $dao = \jDao::get(self::$daoName, $this->daoProfile);
        $dao->insert($newAccount);
    }

    /**
     * Creates a new account from the authenticated user
     *
     * Uses the claim-mapping configuration to map identity attributes
     * to account fields.
     *
     * @param AuthUser $user The user to create
     *
     * @return Account|null The new account or null if account already exists.
     */
    public function createAccountFromAuthUser($user, $status = null)
    {
        $mappedFields = $this->claimMapping->mapAuthUserToAccountFields($user);

        $login = $mappedFields['username'] ?? $user->getLogin();
        if ($this->accountExists($login)) {
            return null;
        }

        if ($status === null) {
            $status = self::STATUS_VALID;
        }

        $email = $mappedFields['email'] ?? $user->getEmail();
        $newAccount = $this->createAccountObject($login, $email);

        foreach ($mappedFields as $field => $value) {
            if ($field === 'username' || $field === 'email') {
                continue;
            }
            if (property_exists($newAccount, $field)) {
                $newAccount->$field = $value;
            }
        }

        if (empty($newAccount->firstname) && empty($newAccount->lastname)) {
            $displayName = $user->getName();
            if ($displayName && $displayName !== $login) {
                $names = explode(' ', $displayName, 2);
                if (count($names) > 1) {
                    $newAccount->firstname = $names[0];
                    $newAccount->lastname = $names[1];
                } else {
                    $newAccount->firstname = '';
                    $newAccount->lastname = $displayName;
                }
            }
        }

        $newAccount->status = $status;
        $this->saveNewAccount($newAccount);
        return new Account($newAccount);
    }

    /**
     * Checks whether an account exists for the given name
     *
     * @param string $username The name to check
     * @return bool true if account exists
     */
    public function accountExists($username)
    {
        if ($this->getAccount($username)) {
            return true;
        }
        return false;
    }

    /**
     * @return Account|null
     */
    public function getCurrentUserAccount()
    {
        $user = jAuthentication::getCurrentUser();
        if (!$user) {
            return null;
        }
        return $this->getAccount($user->getLogin());
    }

    /**
     * Modifies the information of a user
     *
     * @param array $newInfos An associative array containing the information to modify and their new values
     * @param int|string $accountId the account id of the user to modify
     *
     * @return Account|false False if an error occurred, else a new Account object with the updated information
     */
    public function modifyInfos($newInfos, $accountId)
    {
        $dao = \jDao::get(self::$daoName, $this->daoProfile);
        $record = $dao->get($accountId);
        if (!$record) {
            return false;
        }

        foreach ($newInfos as $prop => $value) {
            $record->$prop = $value;
        }

        $dao->update($record);
        return new Account($record);
    }

    public function getAccountList()
    {
        return \jDao::get(self::$daoName, $this->daoProfile)->findAll();
    }

    /**
     * @param string $idpId
     * @param string $authUserId
     * @return null|Account
     */
    public function searchAccountByIdp($idpId, $authUserId, $markUsage = false)
    {
        $daoIdp = \jDao::get(self::$daoIdp, $this->daoProfile);
        $accIdp = $daoIdp->findByIdpAndUser($idpId, $authUserId);
        if (!$accIdp) {
            \jLog::log('search account: no account-idp for '.$idpId. ' - '. $authUserId, 'auth');
            return null;
        }

        if ($markUsage) {
            if ($accIdp->first_used == '') {
                $accIdp->first_used = date('Y-m-d H:i:s');
            }
            $accIdp->last_used = date('Y-m-d H:i:s');
            $accIdp->usage_count++;
            $daoIdp->update($accIdp);
        }

        $dao = \jDao::get(self::$daoName, $this->daoProfile);

        $record = $dao->get($accIdp->account_id);
        if (!$record) {
            \jLog::log('search account: no account for account '.$accIdp->username, 'auth');
            return null;
        }
        return new Account($record);
    }

    /**
     * @param integer $accountId
     * @return \Iterator
     */
    public function searchIdpUsedByAccount($accountId)
    {
        $daoIdp = \jDao::get(self::$daoIdp, $this->daoProfile);
        $list = $daoIdp->findByAccount($accountId);
        return $list;
    }

    /**
     * @param integer $accountId
     * @param string $idpId
     * @return bool
     */
    public function isAccountUsingIdp($accountId, $idpId)
    {
        $daoIdp = \jDao::get(self::$daoIdp, $this->daoProfile);
        $accIdp = $daoIdp->findByIdpAndAccount($idpId, $accountId);
        if ($accIdp) {
            return true;
        }
        return false;
    }

    /**
     * @param integer $accountId
     * @param string $idpId
     * @param string $authUserId
     * @param string $authUserEmail
     * @param object|array $idpData
     * @return void
     */
    public function attachAccountToIdp($accountId, $idpId, $authUserId, $authUserEmail, $idpData = [], $firstUse = false)
    {
        $daoIdp = \jDao::get(self::$daoIdp, $this->daoProfile);
        $recordIdp = $daoIdp->createRecord();
        $recordIdp->idp_id = $idpId;
        $recordIdp->idp_user_id = $authUserId;
        $recordIdp->account_id = $accountId;
        $recordIdp->idp_user_email = $authUserEmail;
        $recordIdp->enabled = true;
        $recordIdp->idp_data = json_encode($idpData);
        if ($firstUse) {
            $recordIdp->first_used = $recordIdp->last_used = date('Y-m-d H:i:s');
            $recordIdp->usage_count = 1;
        }
        $daoIdp->insert($recordIdp);
    }

    /**
     * @param integer $accountId
     * @param string $idpId
     * @param string $authUserId
     * @return void
     * @throws \jException
     */
    public function detachAccountFromIdp($accountId, $idpId, $authUserId)
    {
        $daoIdp = \jDao::get(self::$daoIdp, $this->daoProfile);
        $daoIdp->delete([$idpId, $authUserId, $accountId]);
    }

    public function deleteAccount($accountId)
    {
        $dao = \jDao::get(self::$daoName, $this->daoProfile);
        $dao->delete($accountId);
    }

    /**
     * Returns the full account_idp record for a given IDP and user.
     *
     * Gives access to all fields including idp_data, enabled,
     * idp_user_email, usage_count, etc.
     *
     * @param string $idpId
     * @param string $authUserId
     * @return \jDaoRecordBase|null
     */
    public function getIdpLink($idpId, $authUserId)
    {
        $daoIdp = \jDao::get(self::$daoIdp, $this->daoProfile);
        return $daoIdp->findByIdpAndUser($idpId, $authUserId);
    }

    /**
     * Updates properties on the account_idp record for a given IDP and user.
     *
     * If a property value is an array or object, it will be JSON-encoded
     * (useful for idp_data).
     *
     * @param string $idpId
     * @param string $authUserId
     * @param array $properties associative array of property names and values
     * @return bool true if the record was found and updated
     */
    public function updateIdpLink($idpId, $authUserId, array $properties)
    {
        $daoIdp = \jDao::get(self::$daoIdp, $this->daoProfile);
        $accIdp = $daoIdp->findByIdpAndUser($idpId, $authUserId);
        if (!$accIdp) {
            return false;
        }

        foreach ($properties as $prop => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $accIdp->$prop = $value;
        }

        $daoIdp->update($accIdp);
        return true;
    }
}
