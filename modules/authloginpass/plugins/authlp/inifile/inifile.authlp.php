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
 * it uses an ini file to store credentials and to check authentication
 *
 */
class inifileBackend extends \Jelix\Authentication\LoginPass\BackendAbstract
{

    /**
     * @var string
     */
    protected $iniFile;

    /**
     * @var array
     */
    protected $iniContent;

    protected $isWritable = true;

    public function __construct($params)
    {
        parent::__construct($params);

        if (!isset($params['inifile']) || $params['inifile'] == '') {
            throw new \Exception('Missing file name');
        }

        $this->iniFile = \jFile::parseJelixPath($params['inifile']);
        $this->isWritable = is_writable($this->iniFile);
        $this->iniContent = \Jelix\IniFile\Util::read($this->iniFile);

        if ($this->iniContent === false) {
            throw new \Exception('unknown file');
        }
    }

    /**
     * @return integer a combination of FEATURE_* constants
     */
    public function getFeatures()
    {
        if ($this->isWritable) {
            return self::FEATURE_CHANGE_PASSWORD | self::FEATURE_CREATE_USER | self::FEATURE_DELETE_USER;
        }
        else {
            return self::FEATURE_NONE;
        }
    }

    /**
     * @inheritdoc
     */
    public function createUser($login, $password, $email, $name = '')
    {
        if (!$this->isWritable) {
            return false;
        }
        $name = $name ?: $login;
        $section = 'login:'.$login;
        $properties = array(
            'password' => $this->hashPassword($password),
            'email' => $email,
            'name' => $name
        );

        $ini = new \Jelix\IniFile\IniModifier($this->iniFile);
        $ini->setValues($properties, $section);
        $ini->save();
        $this->iniContent[$section] = $properties;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteUser($login)
    {
        if (!$this->isWritable) {
            return false;
        }
        $section = 'login:'.$login;
        $ini = new \Jelix\IniFile\IniModifier($this->iniFile);

        if (!$ini->isSection($section)) {
            return true;
        }

        $name = $ini->getValue('name', $section);
        $email = $ini->getValue('email', $section);

        $ini->removeSection($section);
        $ini->save();

        unset($this->iniContent[$section]);

        return new AuthUser($login, array(
            AuthUser::ATTR_NAME =>$name,
            AuthUser::ATTR_EMAIL =>$email,
        ));
    }

    /**
     * @param string $login login, as stored into the inifile
     * @param string $newpassword
     * @return boolean true if the password has been changed
     */
    public function changePassword($login, $newpassword)
    {
        if (!$this->isWritable) {
            return false;
        }

        $section = 'login:'.$login;
        $ini = new \Jelix\IniFile\IniModifier($this->iniFile);
        if (!$ini->isSection($section)) {
            return false;
        }

        $hash = $this->hashPassword($newpassword);
        $ini->setValue('password', $hash, $section);
        $ini->save();
        $this->iniContent[$section]['password'] = $hash;
        return true;
    }

    /**
     * @inheritDoc
     */
    public function verifyAuthentication($login, $password)
    {
        $section = 'login:'.$login;
        if (!isset($this->iniContent[$section]) || !is_array($this->iniContent[$section])) {
            return false;
        }

        $userProperties = $this->iniContent[$section];
        if (!isset($userProperties['password'])) {
            return false;
        }

        $result = $this->checkPassword($password, $userProperties['password']);
        if ($result === false) {
            return false;
        }

        if (is_string($result)) {
            $ini = new \Jelix\IniFile\IniModifier($this->iniFile);
            $ini->setValue('password', $result, $section);
            $ini->save();
        }

        $user = new AuthUser($login, array(
            AuthUser::ATTR_NAME =>$userProperties['name'],
            AuthUser::ATTR_EMAIL =>$userProperties['email'],
        ));
        return $user;
    }

    /**
     * @param string $login
     * @return boolean true if a user with this login exists
     */
    public function userExists($login)
    {
        $section = 'login:'.$login;
        if (!isset($this->iniContent[$section]) || !is_array($this->iniContent[$section])) {
            return false;
        }
        return true;
    }
}
