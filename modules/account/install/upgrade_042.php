<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2024 Laurent Jouanneau
 *
 * @see       https://jelix.org
 * @licence   MIT
 */

class accountModuleUpgrader_042 extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('0.4.2');
    protected $date = '2025-09-01';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $helpers->database()->execSQLScript('sql/add_inactivity_notification.sql');
    }
}
