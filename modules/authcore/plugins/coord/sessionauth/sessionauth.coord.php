<?php

/**
 * @author     Laurent Jouanneau
 * @copyright  2019-2023 Laurent Jouanneau
 * @licence   MIT
 */

/**
 */
class sessionauthCoordPlugin implements jICoordPlugin
{

    protected $config;

    function __construct($conf)
    {
        $this->config = $conf;
    }

    /**
     * @param    array  $params   plugin parameters for the current action
     * @return null|jSelectorAct  if action should change
     */
    public function beforeAction($params)
    {
        $sessionHandler = jAuthentication::session();
        $isLogonned = $sessionHandler->hasSessionUser();

        $needAuth = isset($params['auth.required']) ? ($params['auth.required'] == true) : $this->config['authRequired'];

        /** @var jRequest $request */
        $request = jApp::coord()->request;

        $selector = $this->checkWorkflow($isLogonned);
        if ($selector) {
            // the workflow force to go to a controller of a workflow step
            return $selector;
        }

        try {
            if ($isLogonned) {
                // the user is already authenticated
                $authId = $sessionHandler->getIdentityProviderId();
                $authenticator = jAuthentication::manager()->getIdpById($authId);

                // let's check if the authentication is still valid
                if ($authenticator) {
                    $selector = $authenticator->checkSessionValidity(
                        $request,
                        jAuthentication::getCurrentUser(),
                        $needAuth
                    );
                } else {
                    throw new \jHttp401UnauthorizedException(jLocale::get('authcore~auth.error.not.authenticated'));
                }
            } else {

                // The user is not authenticated, check with all identity provider
                // because some of them may support stateless authentication.
                foreach (jAuthentication::manager()->getIdpList() as $authenticator) {
                    // we may have here a \jHttp401UnauthorizedException
                    $selector = $authenticator->checkSessionValidity(
                        $request,
                        null,
                        $needAuth
                    );
                    if ($selector) {
                        break;
                    }
                }
            }
        } catch (\jHttp401UnauthorizedException $e) {
            $sessionHandler->unsetSessionUser();

            if ($request->isAjax()) {
                $action = $this->config['missingAuthAjaxAction'];
            } else {
                $action = $this->config['missingAuthAction'];
            }

            if ($action) {
                $selector = new jSelectorAct($action);
            } else {
                throw $e;
            }
        }

        return $selector;
    }

    protected function checkWorkflow(&$isLogonned)
    {

        $workflow = jAuthentication::getAuthenticationWorkflow();
        if (!$workflow) {
            return null;
        }

        $selector = null;
        if ($isLogonned) {
            // the user is authenticated, so we must destroy the authentication workflow,
            // as it is not relevant anymore
            jAuthentication::stopAuthenticationWorkflow();
        } else {
            // the user is not authenticated.
            // if needed, force to use the action of the current step of the workflow
            $step = $workflow->getCurrentStep();
            if ($step) {
                $selector = $step->getExpectedAction(jApp::coord()->action);
            } else {
                // no more step, so the workflow has ended
                if ($workflow->isFinished()) {
                    $isLogonned = jAuthentication::session()->setSessionUser(
                        $workflow->getTemporaryUser(),
                        jAuthentication::manager()->getIdpById($workflow->getIdpId())
                    );
                }
                jAuthentication::stopAuthenticationWorkflow();
            }
        }

        return $selector;
    }

    public function beforeOutput()
    {
    }

    public function afterProcess()
    {
    }
}
