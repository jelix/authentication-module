<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */
use \Jelix\Authentication\Core\Session;

class alwaysyesCtrl extends jController {
    /**
     *
     */
    function signin() {
        $rep = $this->getResponse('redirect');

        // FIXME load attributes
        $user = new \Jelix\Authentication\Core\SessionUser('testuser', 'User Test', array());
        Session::setSessionUser($user, 'alwaysyes');
        $rep->action = 'test~default:index';
        return $rep;
    }
}

