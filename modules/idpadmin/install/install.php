<?php

use Jelix\Installer\Module\API\InstallHelpers;
use Jelix\Installer\Module\Installer;

class idpadminModuleInstaller extends Installer
{
    public function install(InstallHelpers $helpers)
    {
        $groupName = 'idpadmin.subject.group';
        // Add rights group
        jAcl2DbManager::createRightGroup($groupName, 'idpadmin~default.rights.group.name');

        // Add right subject
        jAcl2DbManager::createRight('idpadmin.view', 'idpadmin~default.idp.view', $groupName);
        jAcl2DbManager::createRight('idpadmin.edit', 'idpadmin~default.idp.edit', $groupName);

    }
}
