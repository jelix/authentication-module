<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */
require (__DIR__.'/application.init.php');

// Commands for the user of the application
\Jelix\Scripts\ModulesCommands::run();

