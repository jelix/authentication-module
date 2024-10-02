<?php
require_once(__DIR__.'/../testapp/application.init.php');
require_once(__DIR__.'/units/lib/EventDispatcherForTests.php');
require_once(__DIR__.'/units/lib/AuthManagerForTest.php');
require_once(__DIR__.'/units/lib/AccountForTest.php');

jApp::setEnv('jelixtests');
jApp::loadConfig('index/config.ini.php');
if (file_exists(jApp::tempPath())) {
    jAppManager::clearTemp(jApp::tempPath());
} else {
    jFile::createDir(jApp::tempPath(), intval("775",8));
}
