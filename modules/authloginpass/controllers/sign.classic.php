<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2024 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */


class signCtrl extends jController
{

    public $sensitiveParameters = array('password');

    public $pluginParams = array(
        '*' => array('auth.required' => false),
    );

    /**
     * Shows the login form.
     */
    public function in()
    {
        return $this->redirect('authcore~sign:in');
    }

    /**
     * Check credentials given into the form of the loginform zone
     *
     * @return jResponseRedirectUrl
     */
    public function checkCredentials()
    {
        /** @var $idp \loginpassIdentityProvider */
        $idp = jAuthentication::manager()->getIdpById('loginpass');

        /** @var $lpManager \Jelix\Authentication\LoginPass\Manager */
        $lpManager = $idp->getManager();

        $params = array('login' => $this->param('login'), 'failed' => 1, 'urlback' => $this->param('urlback'));
        $failUrl = jUrl::get('authcore~sign:in', $params);

        if (
            $this->request->isPostMethod() &&
            $user = $lpManager->verifyPassword($this->param('login'), $this->param('password'))
        ) {
            $urlBack = $this->param('urlback');
            if ($urlBack == '') {
                $urlBack = $lpManager->getUrlAfterLogin();
            }
            if ($urlBack == '') {
                $urlBack = jApp::urlBasePath();
            }

            $workflow = jAuthentication::startAuthenticationWorkflow($user, $idp);
            $workflow->setFinalUrl($urlBack);
            $workflow->setFailUrl($failUrl);
            return $this->redirectToUrl($workflow->getNextAuthenticationUrl());
        }

        return $this->redirectToUrl($failUrl);
    }
}
