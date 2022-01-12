<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2019 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence    MIT
 */

use Jelix\IniFile\IniModifierInterface;

class authloginpassModuleInstaller extends \Jelix\Installer\Module\Installer
{
    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $cryptokey = \Defuse\Crypto\Key::createNewRandomKey();
        $key = $cryptokey->saveToAsciiSafeString();
        $helpers->getLiveConfigIni()->setValue('persistantEncryptionKey', $key, 'loginpass_idp');

        $this->installBackends($helpers->getConfigIni());
    }

    protected function installBackends(IniModifierInterface $config)
    {
        $backendsNames = $config->getValue('backends', 'loginpass_idp');
        if (!$backendsNames) {
            return;
        }
        if (!is_array($backendsNames)) {
            $backendsNames = array($backendsNames);
        }

        foreach($backendsNames as $bName) {
            $bType = $config->getValue('backendType', 'loginpass:'.$bName);
            $properties = $config->getValues('loginpass:'.$bName);
            switch($bType) {
                case 'inifile':
                    $this->installIniFileBackend($properties);
                    break;
                case 'dbdao':
                    if (!$this->getParameter('nodbdaotablecreation')) {
                        $this->installDbDaoBackend($properties);
                    }
                    break;
            }
        }
    }

    protected function installIniFileBackend($properties) {
        if (!isset($properties['inifile']) || $properties['inifile'] == '') {
            throw new \Exception('Missing ini file path');
        }

        $iniFileName = \jFile::parseJelixPath($properties['inifile']);
        if (!file_exists($iniFileName)) {
            \jFile::write($iniFileName, ";<"."?php die(''); ?>\n;for security reasons, don't remove or modify the first line\n\n");
        }
    }

    protected function installDbDaoBackend($properties) {

        $profile = '';
        if (isset($properties['profile'])) {
            $profile = $properties['profile'];
        }

        if (!isset($properties['dao']) || $properties['dao'] == '') {
            $properties['dao'] = 'authloginpass~user';
        }

        $mapper = new jDaoDbMapper($profile);
        $mapper->createTableFromDao($properties['dao']);
    }

}
