<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2019-2024 Laurent Jouanneau
 *
 * @see       https://jelix.org
 * @licence   MIT
 */
class authcoreModuleInstaller extends \Jelix\Installer\Module\Installer
{
    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $mapper = new jDaoDbMapper();
        $mapper->createTableFromDao('authcore~auth_user_requests');
    }
}
