<?php

use Jelix\IniFile\IniModifierInterface;

class accountModuleInstaller extends \Jelix\Installer\Module\Installer
{
    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $cryptokey = \Defuse\Crypto\Key::createNewRandomKey();
        $key = $cryptokey->saveToAsciiSafeString();
        $helpers->getLiveConfigIni()->setValue('persistantEncryptionKey', $key, 'account_idp');

        $this->installBackends($helpers->getConfigIni());
    }

    protected function installBackends(IniModifierInterface $config)
    {
        $backendsNames = $config->getValue('backends', 'account_idp');
        if (!$backendsNames) {
            return;
        }
        if (!is_array($backendsNames)) {
            $backendsNames = array($backendsNames);
        }

        foreach($backendsNames as $bName) {
            $bType = $config->getValue('backendType', 'account:'.$bName);
            $properties = $config->getValues('account:'.$bName);
            switch($bType) {
                case 'dbdao':
                    $this->installDbDaoBackend($properties);
                    break;
            }
        }
    }

    protected function installDbDaoBackend($properties) {

        $profile = '';
        if (isset($properties['profile'])) {
            $profile = $properties['profile'];
        }

        if (!isset($properties['dao']) || $properties['dao'] == '') {
            $properties['dao'] = 'account~accounts';
        }

        $mapper = new jDaoDbMapper($profile);
        $mapper->createTableFromDao($properties['dao']);
    }

}
