<?php

/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 *
 * @link     https://jelix.org
 * @licence MIT
 */

use \Jelix\Authentication\Core\Session;

class LoginFormZone extends jZone
{
    protected $_tplname = 'login.form';

    protected function _prepareTpl()
    {
        $this->_tpl->assign('login', $this->param('login', ''));
        $this->_tpl->assign('failed', $this->param('failed', 0));

        $this->_tpl->assign('isAuthenticated', jAuthentication::isCurrentUserAuthenticated());
        $this->_tpl->assign('user', jAuthentication::getCurrentUser());
    }
}
