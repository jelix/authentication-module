<?php
/**
* @author    Laurent Jouanneau <laurent@jelix.org>
* @copyright 2007-2024 Laurent Jouanneau
*
* @link      https://jelix.org
* @licence   MIT
*/

use Jelix\Authentication\LoginPass\Config as LoginPassConfig;

class passwordEditCtrl extends jController
{
    public $pluginParams = array(
        '*' => array('auth.required' => true),
    );

    protected function checkLoginPassConfAllowEdit()
    {
        $loginPassConfig = new LoginPassConfig(\jApp::config());
        if (!$loginPassConfig->isPasswordChangeEnabled()) {

            throw new jHttp403ForbiddenException();
        }
    }

    public function show()
    {
        $this->checkLoginPassConfAllowEdit();
        $rep  = $this->getResponse('html');
        $form = jForms::get('password_edit');
        if ($form == null) {
            $form = jForms::create('password_edit');
        }
        $tpl = new jTpl();
        $tpl->assign('form', $form);
        $rep->body->assign('MAIN', $tpl->fetch('password_edit'));

        return $rep;
    }

    public function save()
    {
        $this->checkLoginPassConfAllowEdit();
        $form = jForms::fill('password_edit');
        if ($form == null) {
            return $this->redirect('passwordEdit:show');
        }
        if (!$form->check()) {
            return $this->redirect('passwordEdit:show');
        }
        $currentPassword = $form->getData('current_password');
        /** @var \loginpassIdentityProvider $idp */
        $idp = jAuthentication::manager()->getIdpById('loginpass');
        /** @var \Jelix\Authentication\LoginPass\Manager $lpManager */
        $lpManager = $idp->getManager();
        $login = jAuthentication::getCurrentUser()->getLogin();
        $backEnd = $lpManager->getBackendHavingUser($login);
        $isCurrentPassValid = $backEnd->verifyAuthentication($login, $currentPassword);

        if (!$isCurrentPassValid) {
            $form->setErrorOn('current_password', jLocale::get('password.form.create.error.badcurrentpwd'));

            return $this->redirect('passwordEdit:show');
        }
        $backEnd->changePassword($login, $form->getData('new_password'));
        return $this->getResponse('html');
    }
}
