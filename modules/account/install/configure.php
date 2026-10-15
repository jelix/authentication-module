<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2026 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     MIT
 */

use Jelix\Installer\Module\API\ConfigurationHelpers;
use Jelix\Installer\Module\API\LocalConfigurationHelpers;

class accountModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function getDefaultParameters()
    {
        return array(
        );
    }

    public function configure(ConfigurationHelpers $helpers)
    {
        $ini = $helpers->getSingleConfigIni();
        if ($ini->getValue('accountWorkflowAdapter', 'authentication') === null) {
            $ini->setValue('accountWorkflowAdapter', "\Jelix\Authentication\Account\AccountWorkflowAdapterForWorkflow", 'authentication');
        }

        if ($ini->getValue('accountAdapter', 'authentication') === null) {
            $ini->setValue('accountAdapter', "\Jelix\Authentication\Account\AccountAdapter", 'authentication');
        }

        $section = 'account:claim-mapping';
        if ($ini->getValue('username', $section) === null) {
            $ini->setValue('username', 'jelix/identity/username', $section);
            $ini->setValue('displayname', 'jelix/identity/displayname', $section);
            $ini->setValue('firstname', 'jelix/identity/givenname', $section);
            $ini->setValue('lastname', 'jelix/identity/surname', $section);
            $ini->setValue('email', 'jelix/identity/emailaddress', $section);
        }
    }

    public function localConfigure(LocalConfigurationHelpers $helpers)
    {
    }
}
