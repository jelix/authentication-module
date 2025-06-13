<?php
/**
* @author    Laurent Jouanneau <laurent@jelix.org>
* @copyright 2007-2024 Laurent Jouanneau
*
* @link      https://jelix.org
* @licence   MIT
*/

use Jelix\Authentication\LoginPass\FormPassword;
use Jelix\Authentication\LoginPass\Manager;
use Jelix\Authentication\LoginPass\PasswordReset;
use Jelix\Authentication\LoginPass\PasswordResetException;
use Jelix\Authentication\RequestConfirmation\RequestException;

/**
 * controller for the password reset process, when a user has forgotten his
 * password, and want to change it
 */
class password_resetCtrl extends \Jelix\Authentication\LoginPass\AbstractPasswordController
{

    public $pluginParams = array(
        '*' => array('auth.required' => false),
    );

    /**
     * form to request a password reset.
     */
    public function index()
    {
        $repError = $this->_check();
        if ($repError) {
            return $repError;
        }

        $rep = $this->_getLoginPassResponse(jLocale::get('password.form.title'), jLocale::get('password.page.title'));

        $form = jForms::get('password_reset');
        if ($form == null) {
            $form = jForms::create('password_reset');
            $email = $this->param('email');
            if ($email) {
                $form->setData('pass_email', $email);
            }
        }

        $tpl = new jTpl();
        $tpl->assign('form', $form);

        $rep->body->assign('MAIN', $tpl->fetch('password_reset_form'));

        return $rep;
    }

    /**
     * Send an email to reset the password.
     */
    public function send()
    {
        $repError = $this->_check();
        if ($repError) {
            return $repError;
        }

        $rep = $this->getResponse('redirect');
        $rep->action = 'password_reset:index';

        jForms::destroy('password_reset_code');
        jForms::destroy('password_reset_change');
        $form = jForms::fill('password_reset');
        if (!$form) {
            return $this->badParameters();
        }

        if (!$form->check()) {
            return $this->redirect('password_reset:index');
        }

        $email = $form->getData('pass_email');

        $user = $this->passwordReset->findUser($email);
        if (!$user) {
            \jLog::log('A password reset is attempted for unknown user. No user having the email  "'.$email.'"', 'auth');
            // bad given email, ignore the error, so no chance to discover
            // if a login is associated to an email or not
            jForms::destroy('password_reset');
            $reqId = $this->passwordReset->sendFakeEmail();

            return $this->redirect('password_reset:code', array('request_id'=> $reqId));
        }

        // now send the email
        try {
            $reqId = $this->passwordReset->sendEmail($user);
        }
        catch (PasswordResetException $e) {
            /*$errCode = $e->getCode();
            if ($errCode != PasswordResetException::CODE_BAD_LOGIN_EMAIL) {
                $form->setErrorOn('pass_email', $e->getMessage());
                return $this->redirect('password_reset:index');
            }*/
            $reqId = $this->passwordReset->sendFakeEmail();
        }
        catch (\Exception $e)
        {
            \jLog::logEx($e, 'error');
            $form->setErrorOn('pass_email', \jLocale::get('authloginpass~password.form.change.error.unknown'));
            return $this->redirect('password_reset:index');
        }

        jForms::destroy('password_reset');
        return $this->redirect('password_reset:code', array('request_id'=> $reqId));

    }

    /**
     * Display the form to enter the code received by email
     *
     * @return jResponse|jResponseHtml|jResponseJson|jResponseRedirect|void
     * @throws Exception
     * @throws jExceptionSelector
     */
    public function code()
    {
        $repError = $this->_check();
        if ($repError) {
            return $repError;
        }

        $rep = $this->_getLoginPassResponse(jLocale::get('password.form.title'), jLocale::get('password.page.title'));

        $requestId = $this->param('request_id');
        $confRequests = new \Jelix\Authentication\RequestConfirmation\Requests();
        $req = $confRequests->getRequest($requestId);

        $form = jForms::get('password_reset_code');
        if (!$form) {
            $form = jForms::create('password_reset_code');
        }

        $tpl = new jTpl();
        $tpl->assign('requestId', $requestId);
        $tpl->assign('form', $form);
        $tpl->assign('error_status', '');
        $tpl->assign('email', ($req?$req->getEmail():''));

        $rep->body->assign('MAIN', $tpl->fetch('password_reset_code'));
        return $rep;
    }

    /**
     * verify the code received by email
     */
    public function checkcode()
    {
        $repError = $this->_check();
        if ($repError) {
            return $repError;
        }
        $requestId = $this->param('request_id');
        $form = jForms::fill('password_reset_code');
        if (!$form || !$form->check()) {
            return $this->redirect('password_reset:code', array('request_id'=> $requestId));
        }

        try {
            $userRequest = $this->passwordReset->checkKey($requestId, $form->getData('pcode_code'));
        }
        catch (PasswordResetException | RequestException $e) {
            $form->setErrorOn('pcode_code', $e->getMessage());
            return $this->redirect('password_reset:code', array('request_id'=> $requestId));
        }

        jForms::destroy('password_reset_code');
        return $this->redirect('password_reset:resetpassword', array('request_id'=> $requestId));
    }


    /**
     * form to confirm and change the password
     */
    public function resetpassword()
    {
        $repError = $this->_check();
        if ($repError) {
            return $repError;
        }

        $rep = $this->_getLoginPassResponse(jLocale::get($this->formPasswordTitle), jLocale::get($this->pagePasswordTitle));
        $tpl = new jTpl();

        $requestId = $this->param('request_id');
        $userRequest = $this->passwordReset->isConfirmationCodeChecked($requestId);
        if (!$userRequest) {
            $tpl->assign('error_status', jLocale::get('authcore~auth.request.confirmation.error.alreadydone'));
            $rep->body->assign('MAIN', $tpl->fetch($this->formPasswordTpl));
            return $rep;
        }

        $form = jForms::get('password_reset_change');
        if ($form == null) {
            $form = jForms::create('password_reset_change');
        }

        $tpl->assign('passwordWidget', FormPassword::getWidget($form, 'pchg_password'));
        $tpl->assign('error_status', '');
        $tpl->assign('form', $form);
        $tpl->assign('requestId', $requestId);

        $rep->body->assign('MAIN', $tpl->fetch($this->formPasswordTpl));

        return $rep;
    }

    /**
     * Save a new password after a reset request
     */
    public function save()
    {
        $repError = $this->_check();
        if ($repError) {
            return $repError;
        }

        $requestId = $this->param('request_id');
        $userRequest = $this->passwordReset->isConfirmationCodeChecked($requestId);
        if (!$userRequest || !$this->request->isPostMethod()) {
            return $this->redirect('password_reset:resetpassword', array('request_id'=> $requestId));
        }

        $form = jForms::fill('password_reset_change');
        if ($form == null) {
            return $this->redirect('password_reset:resetpassword', array('request_id'=> $requestId));
        }

        if (!FormPassword::checkPassword($form->getData('pchg_password'))) {
            $form->setErrorOn('pchg_password', jLocale::get('jelix~jforms.password.not.strong.enough'));
        }

        if (!$form->check()) {
            return $this->redirect('password_reset:resetpassword', array('request_id'=> $requestId));
        }

        $passwd = $form->getData('pchg_password');
        jForms::destroy('password_reset_change');

        $this->passwordReset->changePassword($userRequest, $passwd);

        return $this->redirect('password_reset:changed');
    }

    /**
     * Page which confirm that the password has changed.
     */
    public function changed()
    {
        $rep = $this->_getLoginPassResponse(jLocale::get($this->formPasswordTitle), jLocale::get($this->pagePasswordTitle));
        $tpl = new jTpl();
        $tpl->assign('title', $rep->title);
        $rep->body->assign('MAIN', $tpl->fetch('password_reset_ok'));

        return $rep;
    }

}
