<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2024 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\LoginPass;

/**
 * Acl adapter for the ldap IP, using jAcl2
 */
class LdapAcl2Adapter implements LdapAclAdapterInterface
{

    public function synchronizeAclGroups($login, $userGroups, $keepUserInDefaultGroups=false)
    {
        if ($keepUserInDefaultGroups) {
            // Add default groups
            $gplist = \jDao::get('jacl2db~jacl2group', 'jacl2_profile')
                ->getDefaultGroups();
            foreach ($gplist as $group) {
                $idx = array_search($group->name, $userGroups);
                if ($idx === false) {
                    $userGroups[] = $group->name;
                }
            }
        }

        // we know the user group: we should be sure it is the same in jAcl2
        $gplist = \jDao::get('jacl2db~jacl2groupsofuser', 'jacl2_profile')
            ->getGroupsUser($login);
        $groupsToRemove = array();
        foreach ($gplist as $group) {
            if ($group->grouptype == 2) { // private group
                continue;
            }
            $idx = array_search($group->name, $userGroups);
            if ($idx !== false) {
                unset($userGroups[$idx]);
            } else {
                $groupsToRemove[] = $group->name;
            }
        }
        foreach ($groupsToRemove as $group) {
            \jAcl2DbUserGroup::removeUserFromGroup($login, $group);
        }
        foreach ($userGroups as $newGroup) {
            if (\jAcl2DbUserGroup::getGroup($newGroup)) {
                \jAcl2DbUserGroup::addUserToGroup($login, $newGroup);
            }
        }
    }
}