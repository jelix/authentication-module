<?php

class accountModuleInstaller extends \Jelix\Installer\Module\Installer
{
    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $mapper = new jDaoDbMapper();
        $mapper->createTableFromDao('account~accounts');
        $mapper->createTableFromDao('account~account_idp');
    }

}
