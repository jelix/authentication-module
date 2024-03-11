<?php
/**
* @copyright 2024 Laurent Jouanneau and other contributors
* @license   MIT
*/


class accountadminModuleInstaller extends \Jelix\Installer\Module\Installer {

    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {
        //$helpers->database()->execSQLScript('sql/install');

        /*
        jAcl2DbManager::createRight('my.right', 'accountadmin~acl.my.right', 'right.group.id');
        jAcl2DbManager::addRight('admins', 'my.right'); // for admin group
        */
    }
}
