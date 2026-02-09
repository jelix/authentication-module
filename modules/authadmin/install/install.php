<?php

use Jelix\Installer\Module\API\InstallHelpers;
use Jelix\Installer\Module\Installer;

class authadminModuleInstaller extends Installer
{
    public function install(InstallHelpers $helpers)
    {
        $groupName = 'auth.idpadmin.subject.group';
        // Add rights group
        jAcl2DbManager::createRightGroup($groupName, 'authadmin~default.rights.group.name');

        // Add right subject
        jAcl2DbManager::createRight('auth.idpadmin.view', 'authadmin~default.idp.view', $groupName);
        jAcl2DbManager::createRight('auth.idpadmin.edit', 'authadmin~default.idp.edit', $groupName);

    }
}
