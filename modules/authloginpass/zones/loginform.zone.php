<?php

/**
 * @author     Laurent Jouanneau
 * @copyright  2019-2024 Laurent Jouanneau
 *
 * @link     https://jelix.org
 * @licence MIT
 */

class LoginFormZone extends jZone
{
    protected $_tplname = 'login.form';

    protected function _prepareTpl()
    {

        if ($this->param('failed')) {
            $this->_tpl->assign('failed', true);
            if (isset($_SESSION['LOGINPASS_ERROR']) && $_SESSION['LOGINPASS_ERROR'] != '') {
                $this->_tpl->assign('errorMessage', $_SESSION['LOGINPASS_ERROR']);
                unset($_SESSION['LOGINPASS_ERROR']);
            }
            else {
                $this->_tpl->assign('errorMessage', jLocale::get('authloginpass~auth.message.failedToLogin'));
            }
        }
        else {
            $this->_tpl->assign('failed', false);
        }


        $this->_tpl->assign('login', $this->param('login') ?: '');

        $this->_tpl->assign('isAuthenticated', jAuthentication::isCurrentUserAuthenticated());
        $this->_tpl->assign('user', jAuthentication::getCurrentUser());

        $passReset = new \Jelix\Authentication\LoginPass\PasswordReset();
        $this->_tpl->assign('passwordResetEnabled', $passReset->isPasswordResetEnabled());
    }
}
