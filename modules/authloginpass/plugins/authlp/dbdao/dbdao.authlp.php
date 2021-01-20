<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */

use Jelix\Authentication\Core\AuthSession\AuthUser;

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
        $record = $this->daoFactory->createRecord();
        $record->login = $login;
        $record->password = $this->hashPassword($password);
        $record->status = 1;
        $record->email = $email;
        $record->username = $name ?: $login;
        $record->attributes = json_encode(array());

        $this->daoFactory->insert($record);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteUser($login)
    {
        $user = $this->daoFactory->getByLogin($login);
        if (!$user) {
            return true;
        }
        if (!$this->daoFactory->deleteByLogin($login)) {
            return false;
        }
        return new AuthUser($login, array(
            AuthUser::ATTR_NAME => $user->username,
            AuthUser::ATTR_EMAIL => $user->email
        ));
    }

    /**
     * @inheritdoc
     */
    public function changePassword($login, $newpassword)
    {
        $dao = jDao::get($this->_params['dao'], $this->_params['profile']);
        return ($dao->updatePassword($login, $this->hashPassword($newpassword)) > 0);
    }

    /**
     *  @inheritdoc
     */
    public function verifyAuthentication($login, $password)
    {
        if (trim($password) == '') {
            return false;
        }

        $userRec = $this->daoFactory->getByLogin($login);
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
        $user = new AuthUser($login, array(
            AuthUser::ATTR_NAME =>$userRec->username,
            AuthUser::ATTR_EMAIL =>$userRec->email,
        ));
        return $user;
    }

    /**
     * @inheritdoc
     */
    public function userExists($login)
    {
        $userRec = $this->daoFactory->getByLogin($login);
        return !!$userRec;
    }

    /**
     * @inheritdoc
     */
    public function getUser($login)
    {
        if (!$this->userExists($login)) {
            return null;
        }
        $userRec = $this->daoFactory->getByLogin($login);
        return new AuthUser($userRec->user_id, array_merge(json_decode($userRec->attributes),
            array(
                AuthUser::ATTR_LOGIN => $userRec->login,
                AuthUser::ATTR_EMAIL => $userRec->email,
                AuthUser::ATTR_NAME => $userRec->username)
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function updateUser($login, $attributes)
    {
        if (!$this->userExists($login)) {
            return ;
        }
        $userRec = $this->daoFactory->getByLogin($login);
        foreach($attributes as $key => $value) {
            if (property_exists($userRec, $key)) {
                $userRec->$key = $value;
            } else {
                $userRec->attributes[$key] = $value;
            }
        }
        $this->daoFactory->update($userRec);
    }
}
