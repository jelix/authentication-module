<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019-2026 Laurent Jouanneau
 * @license   MIT
 */

use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\IdentityAttributes;

/**
 * authentication backend for the authloginpass module
 *
 * it uses a table of a database to store users credentials and to check authentication
 *
 */
class dbdaoBackend extends \Jelix\Authentication\LoginPass\BackendAbstract
{

    /**
     * @var \jDaoFactoryBase
     */
    protected $daoFactory;


    /**
     * It's a cache for the latest user retrieved by userExists.
     *
     * It avoids to do the same query when calling userExists() and then
     * another method that needs the retrieved record.
     *
     * @var \jDaoRecordBase|null the user record retrieved during userExists
     */
    protected $latestExistingUser = null;

    protected static $identityToDao = array(
        IdentityAttributes::UserName => 'login',
        IdentityAttributes::DisplayName => 'realname',
        IdentityAttributes::Email => 'email',
    );

    /**
     * @inheritdoc
     */
    public function __construct($params)
    {
        parent::__construct($params);
        if (!isset($this->_params['profile'])) {
            $this->_params['profile'] = '';
        }
        if (!isset($this->_params['dao']) || $this->_params['dao'] == '') {
            $this->_params['dao'] = 'authloginpass~user';
        }
        $this->daoFactory = \jDao::create($this->_params['dao'], $this->_params['profile']);
    }

    /**
     * @inheritdoc
     */
    public function getFeatures()
    {
        return self::FEATURE_CHANGE_PASSWORD |
            self::FEATURE_CREATE_USER |
            self::FEATURE_DELETE_USER;
    }

    /**
     * @inheritdoc
     */
    public function createUser($login, $password, $email, $name = '')
    {
        $this->latestExistingUser = null;
        $record = $this->daoFactory->createRecord();
        $record->login = $login;
        $record->password = $this->hashPassword($password);
        $record->status = 1;
        $record->email = $email;
        $record->realname = $name ?: $login;
        $record->attributes = '{}';

        $this->daoFactory->insert($record);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteUser($login)
    {
        if (!$this->latestExistingUser || $this->latestExistingUser->login != $login) {
            $user = $this->daoFactory->getByLogin($login);
            if (!$user) {
                return true;
            }
        }
        else {
            $user = $this->latestExistingUser;
            $this->latestExistingUser = null;
        }

        if (!$this->daoFactory->deleteByLogin($login)) {
            return false;
        }
        return $this->getAuthObject($user);
    }

    /**
     * @inheritdoc
     */
    public function changePassword($login, $newpassword)
    {
        $this->latestExistingUser = null;
        $dao = jDao::get($this->_params['dao'], $this->_params['profile']);
        return ($dao->updatePassword($login, $this->hashPassword($newpassword)) > 0);
    }

    /**
     *  @inheritdoc
     */
    public function verifyAuthentication($login, $password)
    {
        $this->latestExistingUser = null;

        if (trim($password) == '') {
            return false;
        }

        $userRec = $this->daoFactory->getByLoginForAuthentication($login);
        if (!$userRec) {
            return false;
        }

        $result = $this->checkPassword($password, $userRec->password);
        if ($result === false) {
            return false;
        }

        if ($result !== true) {
            // it is a new hash for the password, let's update it persistently
            $userRec->password = $result;
            $this->daoFactory->updatePassword($login, $result);
        }

        return $this->getAuthObject($userRec);
    }


    protected function getAuthObject($userRec)
    {
        $attributes = array(
            IdentityAttributes::UserName => $userRec->login,
            IdentityAttributes::DisplayName => $userRec->realname,
            IdentityAttributes::Email => $userRec->email,
        );

        $parsed = $this->parseSessionAttributes();
        if ($parsed === 'ALL') {
            $userProperties = get_object_vars($userRec);
            unset($userProperties['login']);
            unset($userProperties['realname']);
            unset($userProperties['email']);
            unset($userProperties['password']);
            $attributes = array_merge($userProperties, $attributes);
        }
        else if (count($parsed)) {
            $parsed[] = array('field' => 'status', 'key' => 'status');
            $parsed[] = array('field' => 'user_id', 'key' => 'user_id');
            foreach ($parsed as $item) {
                if ($item['field'] == 'login' || $item['field'] == 'realname' || $item['field'] == 'email') {
                    continue;
                }
                if (property_exists($userRec, $item['field'])) {
                    $attributes[$item['key']] = $userRec->{$item['field']};
                }
            }
        }

        // if we need user's attributes, let decode them
        // we don't merge them with the AuthUser attributes to avoid
        // issues and collision. AuthUser attributes and User's attributes
        // don't have the same goal.
        if (isset($attributes['attributes']) && $attributes['attributes'] && is_string($attributes['attributes'])) {
            $attributes['attributes'] = json_decode($attributes['attributes'], true);
        }

        $user = new AuthUser($userRec->login, $attributes);
        return $user;
    }


    /**
     * @inheritdoc
     */
    public function userExists($login)
    {
        $this->latestExistingUser = $this->daoFactory->getByLogin($login);
        return !!$this->latestExistingUser;
    }

    /**
     * @inheritdoc
     */
    public function userWithEmailExists($email)
    {
        $this->latestExistingUser = $this->daoFactory->getByEmail($email);
        if ($this->latestExistingUser) {
            return $this->latestExistingUser->login;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getUser($login)
    {
        if ($this->latestExistingUser && $this->latestExistingUser->login == $login) {
            $userRec = $this->latestExistingUser;
        }
        else {
            $userRec = $this->daoFactory->getByLogin($login);
        }
        $this->latestExistingUser = null;

        return $this->getAuthObject($userRec);

    }

    /**
     * @inheritdoc
     */
    public function updateUser($login, $attributes)
    {
        if ($this->latestExistingUser && $this->latestExistingUser->login == $login) {
            $userRec = $this->latestExistingUser;
        }
        else {
            $userRec = $this->daoFactory->getByLogin($login);
        }
        $this->latestExistingUser = null;
        if (!$userRec) {
            return;
        }

        foreach($attributes as $key => $value) {
            $daoKey = self::$identityToDao[$key] ?? $key;
            if (property_exists($userRec, $daoKey)) {
                $userRec->$daoKey = $value;
            }
        }
        $this->daoFactory->update($userRec);
    }

    /**
     * @return Generator
     */
    public function getUsersList()
    {
        $this->latestExistingUser = null;

        foreach($this->daoFactory->findAll() as $userRec) {
            $user = $this->getAuthObject($userRec);
            yield $user;
        }
    }

}
