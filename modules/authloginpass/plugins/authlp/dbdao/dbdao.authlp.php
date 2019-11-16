<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */


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

        $user = new \Jelix\Authentication\Core\AuthSession\AuthUser($login, $name, array('email'=>$email));
        \jEvent::notify('AuthenticationUserCreation', array(
            'user' => $user,
            'identProviderId' => 'loginpass'
        ));
        return true;
    }

    public function deleteUser($login)
    {
        $user = $this->daoFactory->getByLogin($login);
        if ($user) {
            $this->daoFactory->deleteByLogin($login);
            $user = new \Jelix\Authentication\Core\AuthSession\AuthUser($login, $user->username, array('email'=>$user->email));
            \jEvent::notify('AuthenticationUserDeletion', array(
                'user' => $user,
                'identProviderId' => 'loginpass'
            ));
        }
        return true;
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
            return self::VERIF_AUTH_BAD;
        }

        $userRec = $this->daoFactory->getByLogin($login);
        if (!$userRec) {
            return self::VERIF_AUTH_BAD;
        }

        $result = $this->checkPassword($password, $userRec->password);
        if ($result === false) {
            return self::VERIF_AUTH_BAD;
        }

        if ($result !== true) {
            // it is a new hash for the password, let's update it persistently
            $userRec->password = $result;
            $this->daoFactory->updatePassword($login, $result);
        }

        return self::VERIF_AUTH_OK;
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
