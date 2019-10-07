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
            if (isset(jApp::config()->authentication['idp'])) {
                $idpPlugins = jApp::config()->authentication['idp'];
                if (!is_array($idpPlugins)) {
                    $idpPlugins = array($idpPlugins);
                }
                foreach($idpPlugins as $idpPlugin) {
                    $idpList[] = \jApp::loadPlugin($idpPlugin, 'authidp', '.authidp.php', $idpPlugin.'IdentityProvider', array());
                }
            }
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
        $url = '';
        $idpId = Session::getIdentityProviderId();
        if ($idpId) {
            $idp = jAuthentication::manager()->getIdpById($idpId);
            if ($idp) {
                $url = $idp->getLogoutUrl();
            }
        }
        if ($url == '') {
            $url = self::getSigninPageUrl();
        }
        Session::unsetSessionUser();
        return $url;
    }



}
