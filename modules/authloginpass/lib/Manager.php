<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     https://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\LoginPass;


class Manager
{

    /**
     * @var BackendPluginInterface[]
     */
    protected $backends = array();

    /**
     * Manager constructor.
     * @param string[] $backends
     * @param object $configuration Typically, the configuration object of Jelix
     * @throws \Exception
     */
    public function __construct($backends, $configuration)
    {
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

            if (!isset($backendConfig['backend']) || !$backendConfig['backend']) {
                throw new \Exception("No backend name for loginpass into the section '$backendSection'");
            }
            $backend = $backendConfig['backend'];

            /** @var BackendPluginInterface $plugin */
            $plugin = \jApp::loadPlugin(
                $backend,
                'authlp',
                '.authlp.php',
                $backend.'Backend',
                $backendConfig
            );
            if (is_null($plugin)) {
                throw new \Exception('Plugin "'.$backend.'" for loginpass backend "'.$backendName.'" is not found');
            }
            $plugin->setRegisterKey($backendName);
            $this->backends[$backendName] = $plugin;
        }
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
    protected function getBackendHavingUser($login) {
        foreach($this->backends as $backend) {
            if ($backend->userExists($login)) {
                return $backend;
            }
        }
        return null;
    }

    public function canChangePassword($login)
    {
        $backend = $this->getBackendHavingUser($login);
        if ($backend && ($backend->getFeatures() & BackendPluginInterface::FEATURE_CHANGE_PASSWORD)) {
            return true;
        }
        return false;
    }

    public function changePassword($login, $newpassword)
    {
        $backend = $this->getBackendHavingUser($login);
        if ($backend && $backend->getFeature() & BackendPluginInterface::FEATURE_CHANGE_PASSWORD) {
            return $backend->changePassword($login, $newpassword);
        }
        return false;
    }

    public function verifyPassword($login, $password)
    {
        $backend = $this->getBackendHavingUser($login);
        if ($backend) {
            return $backend->verifyAuthentication($login, $password);
        }

        return false;
    }
}