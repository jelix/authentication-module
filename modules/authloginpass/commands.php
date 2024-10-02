<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */


use Jelix\Authentication\LoginPass\Command;

$application->add(new Command\CreateUserCommand());
$application->add(new Command\DeleteUserCommand());
$application->add(new Command\ChangePasswordCommand());
$application->add(new Command\ListUsersCommand());
$application->add(new Command\ListBackendsCommand());
