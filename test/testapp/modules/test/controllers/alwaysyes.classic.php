<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */
use Jelix\Authentication\Core\AuthSession\AuthUser;

class alwaysyesCtrl extends jController {
    /**
     *
     */
    function signin() {
        $rep = $this->getResponse('redirect');

        $user = new AuthUser('testuser',
            array(
                AuthUser::ATTR_NAME =>'User Test'
            ));
        $idp = jAuthentication::manager()->getIdpById('alwaysyes');
        jAuthentication::session()->setSessionUser($user, $idp);
        $rep->action = 'test~default:index';
        return $rep;
    }
}

