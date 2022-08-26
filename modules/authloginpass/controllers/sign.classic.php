<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2022 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

use \Jelix\Authentication\Core\Session;

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
        $rep = $this->getResponse('htmllogin');
        $rep->title = jLocale::get('authcore~auth.titlePage.login');

        $zp = array(
            'login' => $this->param('login'),
            'failed' => $this->param('failed')
        );

        $rep->body->assignZone('MAIN', 'authloginpass~loginform', $zp);

        return $rep;
    }

    /**
     * Check credentials given into the form of the loginform zone
     *
     * @return jResponseRedirect
     */
    public function checkCredentials()
    {
        $rep = $this->getResponse('redirectUrl');

        /** @var \loginpassIdentityProvider */
        $idp = jAuthentication::manager()->getIdpById('loginpass');

        /** @var \Jelix\Authentication\LoginPass\Manager */
        $lpManager = $idp->getManager();

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
            $rep->url = $workflow->getAuthenticationNextStepUrl();
            return $rep;
        }

        $params = array('login' => $this->param('login'), 'failed' => 1, 'urlback' => $this->param('urlback'));
        $rep->url = jUrl::get('authcore~sign:in', $params);

        return $rep;
    }
}
