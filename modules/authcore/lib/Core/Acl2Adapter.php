<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2023 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */


namespace Jelix\Authentication\Core;

class Acl2Adapter implements \jAcl2AuthAdapterInterface
{

    public function isUserConnected()
    {
        return \jAuthentication::isCurrentUserAuthenticated();
    }

    public function getCurrentUserLogin()
    {
        $user =  \jAuthentication::getCurrentUser();
        if ($user) {
            return $user->getLogin();
        }

        return null;
    }
}
