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
 * modules should call this API, and not underlying classes of authcore
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
        if (self::$session === null) {
            if (
                isset(jApp::config()->authentication['sessionHandler']) &&
                jApp::config()->authentication['sessionHandler'] != ''
            ) {
                $sessHandName = jApp::config()->authentication['sessionHandler'];
            } else {
                $sessHandName = 'php';
            }
            /** @var \Jelix\Authentication\Core\AuthSession\AuthSessionHandlerInterface */
            $sessionHandler = \jApp::loadPlugin($sessHandName, 'authsession', '.authsession.php', $sessHandName . 'AuthSessionHandler', array());
            self::$session = new Jelix\Authentication\Core\AuthSession\AuthSession($sessionHandler);
        }
        return self::$session;
    }

    public static function isCurrentUserAuthenticated()
    {
        return self::session()->hasSessionUser();
    }

    /**
     * @param Workflow\WorkflowState $workflowState
     * @return Workflow\Workflow
     */
    protected static function getWorkflowInstance(Workflow\WorkflowState $workflowState)
    {
        $workflow = new Workflow\Workflow($workflowState);
        $evDispatcher = \jApp::services()->eventDispatcher();
        $steps = array (
            new Workflow\Step\GetAccountStep($evDispatcher, $workflowState),
            new Workflow\Step\CreateAccountStep($evDispatcher, $workflowState),
            new Workflow\Step\SecondFactorAuthStep($evDispatcher, $workflowState),
            new Workflow\Step\AccessValidationStep($evDispatcher, $workflowState),
            new Workflow\Step\AuthDoneStep($evDispatcher, $workflowState),
            new Workflow\Step\AuthFailStep($evDispatcher, $workflowState)
        );

        $transitions = array(
            'account_found' => array(
                'from' => 'get_account',
                'to' => 'second_factor'
            ),
            'account_not_found' => array(
                'from' => 'get_account',
                'to' => 'create_account'
            ),
            'account_not_supported' => array(
                'from' => 'get_account',
                'to' => 'access_validation'
            ),
            'account_created' => array(
                'from' => 'create_account',
                'to' => 'access_validation'
            ),
            'second_factor_success' => array(
                'from' => 'second_factor',
                'to' => 'access_validation'
            ),
            'validation' => array(
                'from' => 'access_validation',
                'to' => 'auth_done'
            ),
            'fail' => array(
                'from' => ['get_account', 'create_account', 'second_factor', 'access_validation', ],
                'to' => 'auth_fail'
            )
        );

        $workflow->setup($steps, $transitions, 'get_account');
        return $workflow;
    }

    public static function startAuthenticationWorkflow(AuthUser $user, IdentityProviderInterface $idp)
    {
        $workflowState = new Workflow\WorkflowState($user, $idp->getId());

        self::session()->setWorkflowState($workflowState);

        $workflow = self::getWorkflowInstance($workflowState);
        $workflow->apply('start');
        return $workflow;
    }

    /**
     * @return Workflow\Workflow
     */
    public static function getAuthenticationWorkflow()
    {

        $workflowState = self::session()->getWorkflowState();
        if (!$workflowState) {
            return null;
        }

        return self::getWorkflowInstance($workflowState);
    }

    public static function stopAuthenticationWorkflow()
    {
        self::session()->unsetWorkflowState();
    }

    public static function getCurrentUser()
    {
        return self::session()->getSessionUser();
    }

    public static function getSigninPageUrl()
    {
        // TODO url should be get from configuration
        return jUrl::get('authcore~sign:in');
    }

    public static function getSignoutPageUrl()
    {
        // TODO url should be get from configuration
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
