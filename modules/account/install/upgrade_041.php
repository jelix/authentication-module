<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2024 Laurent Jouanneau
 *
 * @see       https://jelix.org
 * @licence   MIT
 */

class accountModuleUpgrader_041 extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('0.4.1');
    protected $date = '2025-08-22';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $helpers->database()->execSQLScript('sql/add_notify_auth_success.sql');
    }
}
