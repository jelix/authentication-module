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
        $rep->body->assign('MAIN','<h2>Homepage</h2><p>Welcome on the test page.</p>');
        return $rep;
    }
}

