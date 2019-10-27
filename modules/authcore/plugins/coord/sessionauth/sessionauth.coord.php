<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @licence   MIT
 */

/**
 */
class sessionauthCoordPlugin implements jICoordPlugin {

    protected $config;

    function __construct($conf){
        $this->config = $conf;
    }

    /**
     * @param    array  $params   plugin parameters for the current action
     * @return null or jSelectorAct  if action should change
     */
    public function beforeAction ($params)
    {
        $sessionHandler = jAuthentication::session();
        $isLogonned = $sessionHandler->hasSessionUser();

        $needAuth = isset($params['auth.required']) ? ($params['auth.required']==true):$this->config['authRequired'];
        $selector = null;
        $authManager = jAuthentication::manager();
        /** @var jRequest $request */
        $request = jApp::coord()->request;

        try {
            if ($isLogonned) {
                // the user is already authenticated
                $authId = $sessionHandler->getIdentityProviderId();
                $authenticator = $authManager->getIdpById($authId);

                // let's check if the authentication is still valid
                if ($authenticator) {
                    $selector = $authenticator->checkSessionValidity(
                        $request,
                        jAuthentication::getCurrentUser(),
                        $needAuth
                    );
                }
                else {
                    throw new \jHttp401UnauthorizedException(jLocale::get('authcore~auth.error.not.authenticated'));
                }
            }
            else {
                // the user is not authenticated, check with all identity provider
                foreach ($authManager->getIdpList() as $authenticator) {
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
        }
        catch (\jHttp401UnauthorizedException $e) {
            $sessionHandler->unsetSessionUser();

            if ($request->isAjax()) {
                $action = $this->config['missingAuthAjaxAction'];
            }
            else {
                $action = $this->config['missingAuthAction'];
            }

            if ($action) {
                $selector = new jSelectorAct($action);
            }
            else {
                throw $e;
            }
        }

        return $selector;
    }


    public function beforeOutput(){}

    public function afterProcess (){}


}
