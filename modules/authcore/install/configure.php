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
        if ($ini->getValue('sessionHandler', 'authentication') === null) {
            $ini->setValue('sessionHandler', "php", 'authentication');
        }

        if ($ini->getValue('sessionauth', 'coordplugins') === null) {
            $ini->setValue('sessionauth', true, 'coordplugins');
        }
        if (!$ini->isSection('sessionauth')) {
            $ini->setValues(array(
                'missingAuthAction'=>'',
                'missingAuthAjaxAction'=>'',
                'authRequired' => false
            ), 'sessionauth');
        }
    }

    public function localConfigure(LocalConfigurationHelpers $helpers)
    {
    }
}
