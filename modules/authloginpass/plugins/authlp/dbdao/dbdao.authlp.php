<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019-2022 Laurent Jouanneau
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

        $attributes = array(
            AuthUser::ATTR_NAME =>$userRec->username,
            AuthUser::ATTR_EMAIL =>$userRec->email,
        );


        $sessionAttributes = $this->getConfigurationParameter('sessionAttributes');
        if ($sessionAttributes == 'ALL') {
            $userProperties = get_object_vars($userRec);
            unset($userProperties['username']);
            unset($userProperties['email']);
            $attributes = array_merge($userProperties, $attributes);
        }
        else if ($sessionAttributes != '') {
            $sessionAttributes = preg_split('/\s*,\s*/', $sessionAttributes);
            foreach($sessionAttributes as $prop) {
                if ($prop == AuthUser::ATTR_NAME || $prop == AuthUser::ATTR_EMAIL) {
                    continue;
                }
                if (property_exists($userRec, $prop)) {
                    $attributes[$prop] = $userRec->$prop;
                }
            }
        }

        $user = new AuthUser($login, $attributes);
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
}
