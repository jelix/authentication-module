<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

class alwaysyesCtrl extends jController {
    /**
     *
     */
    function signin() {
        $rep = $this->getResponse('redirect');

        // FIXME load attributes
        $user = new \Jelix\Authentication\Core\AuthSession\AuthUser('testuser', 'User Test', array());
        jAuthentication::session()->setSessionUser($user, 'alwaysyes');
        $rep->action = 'test~default:index';
        return $rep;
    }
}

