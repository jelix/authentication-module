<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

class defaultCtrl extends jController {

    public $pluginParams = array(
        '*' => array('auth.required' => true),
    );

    /**
    *
    */
    function index() {
        $rep = $this->getResponse('html');
        $tpl = new jTpl();
        $tpl->assign('login', jAuthentication::getCurrentUser()->getLogin());
        $rep->body->assign('MAIN', $tpl->fetch('homepage'));
        return $rep;
    }
}

