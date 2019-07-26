<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

$appPath = __DIR__.'/';
require ($appPath.'vendor/autoload.php');

jApp::initPaths(
    $appPath
    //$appPath.'www/',
    //$appPath.'var/',
    //$appPath.'var/log/',
    //$appPath.'var/config/'
);
jApp::setTempBasePath(realpath($appPath.'temp/').'/');

require ($appPath.'vendor/jelix_app_path.php');

jApp::declareModulesDir(array(
                        __DIR__.'/modules/',
                        __DIR__.'/../modules/'
                    ));
