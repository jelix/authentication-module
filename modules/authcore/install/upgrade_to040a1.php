<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2024 Laurent Jouanneau
 *
 * @see       https://jelix.org
 * @licence   MIT
 */

class authcoreModuleUpgrader_to040a1 extends  \Jelix\Installer\Module\Installer
{

    protected $targetVersions = array('0.4.0a1');
    protected $date = '2024-04-11';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $mapper = new jDaoDbMapper();
        $mapper->createTableFromDao('authcore~auth_user_requests');
     }
}