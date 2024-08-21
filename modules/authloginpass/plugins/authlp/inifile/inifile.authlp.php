<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019-2024 Laurent Jouanneau
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
            'name' => $name,
            'status' => 1
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

        $ini->removeSection($section);
        $ini->save();

        $properties = $this->iniContent[$section];
        unset($this->iniContent[$section]);

        return $this->getAuthObject($login, $properties);
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
        return $this->getAuthObject($login, $userProperties);
    }


    protected function getAuthObject($login, $userProperties)
    {
        $attributes = array(
            AuthUser::ATTR_NAME =>$userProperties['name'],
            AuthUser::ATTR_EMAIL =>$userProperties['email'],
        );

        $sessionAttributes = $this->getConfigurationParameter('sessionAttributes');
        if ($sessionAttributes == 'ALL') {
            unset($userProperties[AuthUser::ATTR_NAME]);
            unset($userProperties[AuthUser::ATTR_EMAIL]);
            unset($userProperties[AuthUser::ATTR_LOGIN]);
            unset($userProperties['password']);
            $attributes = array_merge($userProperties, $attributes);
        }
        else if ($sessionAttributes != '') {
            $sessionAttributes = preg_split('/\s*,\s*/', $sessionAttributes);
            // always retrieve the status property, we may need it
            $sessionAttributes[] = 'status';
            foreach($sessionAttributes as $prop) {
                if ($prop == AuthUser::ATTR_NAME || $prop == AuthUser::ATTR_EMAIL || $prop == AuthUser::ATTR_LOGIN) {
                    continue;
                }
                if (isset($userProperties[$prop])) {
                    $attributes[$prop] = $userProperties[$prop];
                }
            }
        }

        $user = new AuthUser($login, $attributes);
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

    /**
     * @inheritDoc
     */
    public function userWithEmailExists($email)
    {
        foreach($this->iniContent as $secName => $userRec) {
            if (strpos($secName , 'login:') !== 0) {
                continue;
            }
            if (isset($userRec['email']) && $userRec['email'] == $email) {
                $login = str_replace('login:', '', $secName);
                return $login;
            }
        }
        return false;
    }


    public function getUser($login)
    {
        if (!$this->userExists($login)) {
            return null;
        }
        $section = 'login:'.$login;
        return $this->getAuthObject($login, $this->iniContent[$section]);
    }

    public function updateUser($login, $attributes)
    {
        if (!$this->userExists($login)) {
            return ;
        }
        $section = 'login:'.$login;
        $ini = new \Jelix\IniFile\IniModifier($this->iniFile);
        $ini->setValues($attributes, $section);
        $ini->save();
    }

    public function getUsersList()
    {
        foreach($this->iniContent as $secName => $userRec) {
            if (strpos($secName , 'login:') !== 0) {
                continue;
            }
            $login = str_replace('login:', '', $secName);
            $user = $this->getAuthObject($login, $userRec);
            yield $user;
        }
    }
}
