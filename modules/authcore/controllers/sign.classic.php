<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2022 Laurent Jouanneau
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
        if (!jAuthentication::isCurrentUserAuthenticated()) {
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
        $rep = $this->getResponse('redirectUrl');
        $rep->url = jAuthentication::signout();
        return $rep;
    }
}
