<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2024 Laurent Jouanneau
 * @license   MIT
 */

namespace Jelix\Authentication\LoginPass;

interface LdapAclAdapterInterface
{
    public function synchronizeAclGroups($login, $userGroups, $keepUserInDefaultGroups=false);
}