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
    }

    public function localConfigure(LocalConfigurationHelpers $helpers)
    {
    }
}
