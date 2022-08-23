<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     https://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\LoginPass;

use Jelix\Authentication\Core\AuthSession\AuthUser;

class Manager
{

    /**
     * @var BackendPluginInterface[]
     */
    protected $backends = array();

    protected $actionAfterLogin = '';

    /**
     * Manager constructor.
     * @param string[] $backends
     * @param object $configuration Typically, the configuration object of Jelix
     * @throws \Exception
     */
    public function __construct($backends, $configuration)
    {
        if (isset($configuration->loginpass_idp['afterLogin'])) {
            $this->actionAfterLogin = $configuration->loginpass_idp['afterLogin'];
        }

        $options = array(
            'passwordHashAlgo'=>1,
            'passwordHashOptions'=> null,
            'deprecatedPasswordCryptFunction'=> '',
            'deprecatedPasswordSalt'=>''
        );

        $commonPropName = 'loginpass:common';
        if (property_exists($configuration, $commonPropName) &&
            is_array($configuration->$commonPropName)
        ) {
            $options = array_merge(
                $options,
                $configuration->$commonPropName
            );
        }

        foreach ($backends as $backendName) {
            // a backend name is a part of a section name
            $backendSection = 'loginpass:'.$backendName;

            if (!property_exists($configuration, $backendSection) ||
                !is_array($configuration->$backendSection)
            ) {
                throw new \Exception("Section '$backendSection' to configure the backend '$backendName' for loginpass, is missing");
            }

            $backendConfig = array_merge($options, $configuration->$backendSection);

            if (!isset($backendConfig['backendType']) || !$backendConfig['backendType']) {
                throw new \Exception("No backend type for loginpass into the section '$backendSection'");
            }
            $backendType = $backendConfig['backendType'];

            /** @var BackendPluginInterface $plugin */
            $plugin = \jApp::loadPlugin(
                $backendType,
                'authlp',
                '.authlp.php',
                $backendType.'Backend',
                $backendConfig
            );
            if (is_null($plugin)) {
                throw new \Exception('Plugin "'.$backendType.'" for loginpass backend "'.$backendName.'" is not found');
            }
            $plugin->setRegisterKey($backendName);
            $this->backends[$backendName] = $plugin;
        }
    }

    public function getUrlAfterLogin() {
        if ($this->actionAfterLogin) {
            return \jUrl::get($this->actionAfterLogin);
        }
        return '';
    }

    /**
     * @return BackendPluginInterface[]
     */
    public function getBackends() {
        return $this->backends;
    }

    /**
     * @param string $name
     * @return BackendPluginInterface|null
     */
    public function getBackendByName($name) {
        if (isset($this->backends[$name])) {
            return $this->backends[$name];
        }
        return null;
    }


    /**
     * @param string $login
     * @return BackendPluginInterface|null
     */
    public function getBackendHavingUser($login) {
        foreach($this->backends as $backend) {
            if ($backend->userExists($login)) {
                return $backend;
            }
        }
        return null;
    }

    public function getFirstBackendName() {
        if (!count($this->backends)) {
            throw new \InvalidArgumentException("No configured backend");
        }
        $backendNames = array_keys($this->backends);
        return array_shift($backendNames);
    }

    public function getFirstBackend() {
        return $this->backends[$this->getFirstBackendName()];
    }

    /**
     * @param string $login
     * @param string $backendName if not given, it tries to find the backend managing the login
     * @return bool true if the password can be changed
     */
    public function canChangePassword($login, $backendName = '')
    {
        if ($backendName) {
            $backend = $this->getBackendByName($backendName);
        }
        else {
            $backend = $this->getBackendHavingUser($login);
        }

        if ($backend && ($backend->getFeatures() & BackendPluginInterface::FEATURE_CHANGE_PASSWORD)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $login
     * @param string $backendName if not given, it tries to find the backend managing the login
     * @return bool true if the password has been changed
     */
    public function changePassword($login, $newpassword, $backendName = '')
    {
        if ($backendName) {
            $backend = $this->getBackendByName($backendName);
        }
        else {
            $backend = $this->getBackendHavingUser($login);
        }
        if ($backend && $backend->getFeatures() & BackendPluginInterface::FEATURE_CHANGE_PASSWORD) {
            return $backend->changePassword($login, $newpassword);
        }
        return false;
    }

    /**
     * @param $login
     * @param $password
     * @param string $backendName if not given, it tries to find the backend managing the login
     * @return false|AuthUser
     */
    public function verifyPassword($login, $password, $backendName = '')
    {
        if ($backendName) {
            $backend = $this->getBackendByName($backendName);
        }
        else {
            $backend = $this->getBackendHavingUser($login);
        }
        if ($backend) {
            $user = $backend->verifyAuthentication($login, $password);
            if (is_object($user)) {
               return $user;
            }
        }
        \jEvent::notify('AuthenticationFail', array(
            'login' => $login
        ));
        return false;
    }

    public function createUser($login, $password, array $attributes, $backendName = '')
    {
        if ($backendName) {
            $backend = $this->getBackendByName($backendName);
        }
        else {
            $backend = $this->getFirstBackend();
        }
        if (!$backend->hasFeature(BackendPluginInterface::FEATURE_CREATE_USER)) {
            throw new \Exception('The backend doesn\'t support user creation');
        }

        $user = new AuthUser($login, $attributes);

        if ($backend->createUser($login, $password, $user->getEmail(), $user->getName())) {
            \jEvent::notify('AuthenticationUserCreation', array(
                'user' => $user,
                'identProvider' => \jAuthentication::manager()->getIdpById('loginpass')
            ));
            return true;
        }

        return false;

    }

    public function deleteUser($login, $backendName = '')
    {
        if ($backendName) {
            $backend = $this->getBackendByName($backendName);
        }
        else {
            $backend = $this->getFirstBackend();
        }

        if (!$backend->hasFeature(BackendPluginInterface::FEATURE_DELETE_USER)) {
            throw new \Exception('The backend doesn\'t support user deletion');
        }

        $user = $backend->deleteUser($login);
        if (!is_object($user)) {
            return $user;
        }

        \jEvent::notify('AuthenticationUserDeletion', array(
            'user' => $user,
            'identProvider' => \jAuthentication::manager()->getIdpById('loginpass')
        ));
        return true;
    }

    /**
     * Looks for the user corresponding to $login in all the backends
     * 
     * @param string $login The login to search
     * @return AuthUser|null The user corresponding to login, null if not found.
     */
    public function getUser($login) {
        $back = $this->getBackendHavingUser($login);
        if (!$back) {
            return null;
        }
        $user = $back->getUser($login);
        return $user;
    }

    /**
     * Updates $user's infos
     * 
     * @param AuthUser $user The user to modify
     */
    public function updateUser($user)
    {
        $login = $user->getLogin();
        $back = $this->getBackendHavingUser($login);
        if (!$back) {
            return ;
        }
        $back->updateUser($login, $user->getAttributes());
    }
}
