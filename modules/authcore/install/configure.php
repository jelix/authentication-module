<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2019 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     MIT
 */
use Jelix\Installer\Module\API\ConfigurationHelpers;
use Jelix\Installer\Module\API\LocalConfigurationHelpers;

class authcoreModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function getDefaultParameters()
    {
        return array(
        );
    }

    public function configure(ConfigurationHelpers $helpers)
    {
        $ini = $helpers->getSingleConfigIni();
        if ($ini->getValue('idp', 'authentication') === null) {
            $ini->setValue('idp', "", 'authentication');
        }

    }

    public function localConfigure(LocalConfigurationHelpers $helpers)
    {
    }
}
