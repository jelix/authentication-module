<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

use Jelix\Authentication\Core\AuthenticatorManager;
use Jelix\Authentication\Core\Session;

/**
 * Proxy class to access to authentication features in a Jelix framework context
 *
 * modules should call this API, and not underlying classes of authcore
 */
class jAuthentication {

    /**
     * @var AuthenticatorManager
     */
    protected static $manager = null;

    /**
     * @return AuthenticatorManager
     */
    public static function manager() {
        if (self::$manager === null) {
            $idpList = array();

            self::$manager = new AuthenticatorManager($idpList);
        }
        return self::$manager;
    }

    public static function isCurrentUserAuthenticated() {
        return Session::hasSessionUser();
    }

    public static function getCurrentUser()  {
        return Session::getSessionUser();
    }

    public static function getSigninPageUrl() {
        // TODO url should be get from configuration
        return jUrl::get('authcore~sign:in');
    }

    public static function getSignoutPageUrl() {
        // TODO url should be get from configuration
        return jUrl::get('authcore~sign:out');
    }


    /**
     * disconnect the user.
     *
     * It returns an url where to redirect to finish the sign up
     * @return string url
     */
    public static function signout() {
        $url = self::getSigninPageUrl();
        $idpId = Session::getIdentityProviderId();
        if ($idpId) {
            $idp = jAuthentication::manager()->getIdpById($idpId);
            $url = $idp->getLogoutUrl();
        }

        Session::unsetSessionUser();
        return $url;
    }



}