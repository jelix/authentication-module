<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication;

/**
 * Manage the authenticated session
 *
 */
class Session {

    const SESSION_NAME = 'JAUTH';

    /**
     * @param SessionUser $user
     * @param string $IPid
     */
    public static function setSessionUser(SessionUser $user, $IPid)
    {
        $_SESSION[self::SESSION_NAME] = array(
            'user' => $user,
            'identProviderId' => $IPid
        );
        \jEvent::notify('AuthenticationLogin', array(
            'user' => $user,
            'identProviderId' => $IPid
        ));
    }

    public static function unsetSessionUser()
    {
        if (isset($_SESSION[self::SESSION_NAME])) {
            $user = $_SESSION[self::SESSION_NAME]['user'];
            unset($_SESSION[self::SESSION_NAME]);
            \jEvent::notify('AuthenticationLogout', array('user' => $user));
        }
    }

    public static function hasSessionUser()
    {
        return isset($_SESSION[self::SESSION_NAME]);
    }

    /**
     * @return SessionUser|null
     */
    public static function getSessionUser()
    {
        if (isset($_SESSION[self::SESSION_NAME])) {
            return $_SESSION[self::SESSION_NAME]['user'];
        }
        return null;
    }

    public static function getIdentityProviderId()
    {
        if (isset($_SESSION[self::SESSION_NAME])) {
            return $_SESSION[self::SESSION_NAME]['identProviderId'];
        }
        return null;
    }


}
