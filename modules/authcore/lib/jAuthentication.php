<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @licence MIT
 */

use Jelix\Authentication\Core\AuthenticatorManager;
use Jelix\Authentication\Core\AuthSession\AuthSession;
use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\IdentityProviderInterface;
use Jelix\Authentication\Core\Workflow;

/**
 * Proxy class to access to authentication features in a Jelix framework context
 *
 * modules should call this API, instead of underlying classes of authcore
 */
class jAuthentication
{

    /**
     * @var AuthenticatorManager
     */
    protected static $manager = null;

    /**
     * @var AuthSession
     */
    protected static $session = null;

    /**
     * @var Workflow\StandardWorkflow
     */
    protected static $workflow = null;

    /**
     * @return AuthenticatorManager
     */
    public static function manager()
    {
        if (self::$manager === null) {
            $idpList = array();
            if (isset(jApp::config()->authentication['idp'])) {
                $idpPlugins = jApp::config()->authentication['idp'];
                if (!is_array($idpPlugins)) {
                    $idpPlugins = array($idpPlugins);
                }
                foreach ($idpPlugins as $idpPlugin) {
                    $options = array();
                    $optionName = $idpPlugin . '_idp';
                    if (isset(jApp::config()->$optionName)) {
                        $options = jApp::config()->$optionName;
                    }
                    $idpList[] = \jApp::loadPlugin($idpPlugin, 'authidp', '.authidp.php', $idpPlugin . 'IdentityProvider', $options);
                }
            }
            self::$manager = new AuthenticatorManager($idpList);
        }
        return self::$manager;
    }

    /**
     * @return AuthSession
     */
    public static function session()
    {
        if (static::$session === null) {
            if (
                isset(jApp::config()->authentication['sessionHandler']) &&
                jApp::config()->authentication['sessionHandler'] != ''
            ) {
                $sessHandName = jApp::config()->authentication['sessionHandler'];
            } else {
                $sessHandName = 'php';
            }
            /** @var $sessionHandler \Jelix\Authentication\Core\AuthSession\AuthSessionHandlerInterface */
            $sessionHandler = \jApp::loadPlugin($sessHandName, 'authsession', '.authsession.php', $sessHandName . 'AuthSessionHandler', array());
            $evDispatcher = \jApp::services()->eventDispatcher();
            static::$session = new AuthSession($sessionHandler, $evDispatcher);
        }
        return static::$session;
    }

    public static function isCurrentUserAuthenticated()
    {
        return self::session()->hasSessionUser();
    }

    /**
     * @return Workflow\StandardWorkflow
     */
    protected static function standardWorkflow()
    {
        if (!self::$workflow) {
            self::$workflow = new Workflow\StandardWorkflow(
                self::session(),
                \jApp::services()->eventDispatcher()
            );
        }
        return self::$workflow;
    }


    public static function startAuthenticationWorkflow(AuthUser $user, IdentityProviderInterface $idp)
    {
        $workflowState = new Workflow\WorkflowState($user, $idp->getId());

        return self::standardWorkflow()->start($workflowState);
    }

    /**
     * @return Workflow\Workflow
     */
    public static function getAuthenticationWorkflow()
    {
        return self::standardWorkflow()->getWorkflow();
    }


    public static function stopAuthenticationWorkflow()
    {
        self::standardWorkflow()->stop();
    }


    public static function checkWorkflowAndAction(&$isLogonned, jIActionSelector $action)
    {
        return self::standardWorkflow()->checkWorkflowAndAction($isLogonned, $action, self::manager());
    }

    public static function getCurrentUser()
    {
        return self::session()->getSessionUser();
    }

    public static function getSigninPageUrl()
    {
        $config = jApp::config()->authentication;
        if (isset($config['signInAction']) && $config['signInAction']) {
            return jUrl::get($config['signInAction']);
        }
        return jUrl::get('authcore~sign:in');
    }

    public static function getSignoutPageUrl()
    {
        $config = jApp::config()->authentication;
        if (isset($config['signOutAction']) && $config['signOutAction']) {
            return jUrl::get($config['signOutAction']);
        }
        return jUrl::get('authcore~sign:out');
    }


    /**
     * disconnect the user.
     *
     * It returns an url where to redirect to finish the sign up
     * @return string url
     */
    public static function signout()
    {
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
