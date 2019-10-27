<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

use Jelix\Authentication\Core\AuthenticatorManager;
use Jelix\Authentication\Core\AuthSession\AuthSession;

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
     * @var AuthSession
     */
    protected static $session = null;

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
                    $options = array();
                    $optionName = $idpPlugin.'_idp';
                    if (isset(jApp::config()->$optionName)) {
                        $options = jApp::config()->$optionName;
                    }
                    $idpList[] = \jApp::loadPlugin($idpPlugin, 'authidp', '.authidp.php', $idpPlugin.'IdentityProvider', $options);
                }
            }
            self::$manager = new AuthenticatorManager($idpList);
        }
        return self::$manager;
    }

    /**
     * @return AuthSession
     */
    public static function session() {
        if (self::$session === null) {
            if (isset(jApp::config()->authentication['sessionHandler']) &&
                jApp::config()->authentication['sessionHandler'] != ''
            ) {
                $sessHandName = jApp::config()->authentication['sessionHandler'];
            }
            else {
                $sessHandName = 'php';
            }
            $sessionHandler = \jApp::loadPlugin($sessHandName, 'authsession', '.authsession.php', $sessHandName.'AuthSessionHandler', array());
            self::$session = new Jelix\Authentication\Core\AuthSession\AuthSession($sessionHandler);

        }
        return self::$session;
    }

    public static function isCurrentUserAuthenticated() {
        return self::session()->hasSessionUser();
    }

    public static function getCurrentUser()  {
        return self::session()->getSessionUser();
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
        $idpId = self::session()->getIdentityProviderId();
        if ($idpId) {
            $idp = jAuthentication::manager()->getIdpById($idpId);
            if ($idp) {
                $url = $idp->getLogoutUrl();
            }
        }
        if ($url == '') {
            $url = self::getSigninPageUrl();
        }
        self::session()->unsetSessionUser();
        return $url;
    }



}
