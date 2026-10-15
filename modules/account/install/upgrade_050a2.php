<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2026 Laurent Jouanneau
 *
 * @see       https://jelix.org
 * @licence   MIT
 */

class accountModuleUpgrader_050a2 extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('0.5.0-alpha.2');
    protected $date = '2026-10-16';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $ini = $helpers->getConfigIni();
        if ($ini->getValue('accountWorkflowAdapter', 'authentication') === null) {
            $ini->setValue('accountWorkflowAdapter', "\Jelix\Authentication\Account\AccountWorkflowAdapterForWorkflow", 'authentication');
        }

        if ($ini->getValue('accountAdapter', 'authentication') === null) {
            $ini->setValue('accountAdapter', "\Jelix\Authentication\Account\AccountAdapter", 'authentication');
        }

        $section = 'account:claim-mapping';
        if ($ini->getValue('username', $section) === null) {
            $ini->setValue('username', 'jelix/identity/username', $section);
            $ini->setValue('firstname', 'jelix/identity/givenname', $section);
            $ini->setValue('lastname', 'jelix/identity/surname', $section);
            $ini->setValue('email', 'jelix/identity/emailaddress', $section);
        }

        $helpers->database()->execSQLScript('sql/alter_column_firstname_lastname');
    }
}
