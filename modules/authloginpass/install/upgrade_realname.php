<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2023 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence    MIT
 */

use Jelix\IniFile\IniModifierInterface;

class authloginpassModuleUpgrader_realname extends \Jelix\Installer\Module\Installer
{

    protected $targetVersions = array('0.3.0-alpha.1');

    protected $date = '2023-05-29';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $config = $helpers->getConfigIni();
        $backendsNames = $config->getValue('backends', 'loginpass_idp');
        if (!$backendsNames || $this->getParameter('nodbdaotablecreation')) {
            return;
        }
        if (!is_array($backendsNames)) {
            $backendsNames = array($backendsNames);
        }

        $db = $helpers->database();
        foreach($backendsNames as $bName) {
            $bType = $config->getValue('backendType', 'loginpass:'.$bName);
            $properties = $config->getValues('loginpass:'.$bName);
            if ($bType == 'dbdao') {
                $profile = '';
                if (isset($properties['profile'])) {
                    $profile = $properties['profile'];
                }
                $db->useDbProfile($profile);

                if (!isset($properties['dao']) || $properties['dao'] != 'authloginpass~user') {
                    continue;
                }

                $dao = jDao::get($properties['dao']);
                $ptable = $dao->getTables()[$dao->getPrimaryTable()]['realname'];

                $table = $db->dbConnection()->schema()->getTable($ptable);
                $oldColumn = $table->getColumn('username');
                $newColumn = $table->getColumn('realname');
                if ($oldColumn && !$newColumn) {
                    $oldColumn->name = 'realname';
                    $table->alterColumn($oldColumn, 'username');
                }
            }
        }
    }


}
