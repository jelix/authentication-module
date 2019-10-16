<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

use Jelix\Authentication\Core\AuthSession\AuthSessionHandlerInterface;
use Jelix\Authentication\Core\AuthSession\AuthUser;


class phpAuthSessionHandler implements AuthSessionHandlerInterface {

    const SESSION_NAME = 'JAUTH';

    /**
     * @param AuthUser $user
     * @param string $IPid
     */
    public function setSessionUser(AuthUser $user, $IPid)
    {
        $_SESSION[self::SESSION_NAME] = array(
            'user' => $user,
            'identProviderId' => $IPid
        );
    }

    public function unsetSessionUser()
    {
        if (isset($_SESSION[self::SESSION_NAME])) {
            unset($_SESSION[self::SESSION_NAME]);
        }
    }

    public function hasSessionUser()
    {
        return isset($_SESSION[self::SESSION_NAME]);
    }

    /**
     * @return AuthUser|null
     */
    public function getSessionUser()
    {
        if (isset($_SESSION[self::SESSION_NAME])) {
            return $_SESSION[self::SESSION_NAME]['user'];
        }
        return null;
    }

    public function getIdentityProviderId()
    {
        if (isset($_SESSION[self::SESSION_NAME])) {
            return $_SESSION[self::SESSION_NAME]['identProviderId'];
        }
        return null;
    }
}

