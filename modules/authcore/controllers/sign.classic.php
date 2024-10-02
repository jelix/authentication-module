<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2023 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

class signCtrl extends jController
{

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

        $tpl = new jTpl();
        if (jAuthentication::isCurrentUserAuthenticated()) {
            $config = jApp::config()->authentication;
            if (isset($config['signInAlreadyAuthAction']) && $config['signInAlreadyAuthAction']) {
                return $this->redirect($config['signInAlreadyAuthAction']);
            }
        } else {
            $manager = jAuthentication::manager();
            $htmlForms = array();
            foreach ($manager->getIdpList() as $idp) {
                $content = $idp->getHtmlLoginForm($this->request);
                if ($content) {
                    $htmlForms[$idp->getId()] = $content;
                }
            }
            $tpl->assign('htmlForms', $htmlForms);
        }

        $rep->body->assign('MAIN', $tpl->fetch('login.block'));

        return $rep;
    }


    public function out()
    {
        return $this->redirectToUrl(jAuthentication::signout());
    }
}
