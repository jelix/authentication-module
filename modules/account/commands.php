<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2023 Laurent Jouanneau
 * @license   MIT
 */


use Jelix\Authentication\Account\Command;

$application->add(new Command\CreateAccountCommand());
$application->add(new Command\AccountListCommand());
$application->add(new Command\LoginCreateCommand());
$application->add(new Command\AccountSetIdpCommand());
$application->add(new Command\AccountUnsetIdpCommand());
$application->add(new Command\AccountIdpListCommand());
