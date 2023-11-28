<?php
/**
* @author       Laurent Jouanneau <laurent@jelix.org>
* @contributor
*
* @copyright    2007-2023 Laurent Jouanneau
*
* @link         http://jelix.org
* @licence      http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

use Jelix\Authentication\LoginPass\Manager;

/**
 * controller for the password reset process, when a user has forgotten his
 * password, and want to change it
 */
class password_resetCtrl extends \Jelix\Authentication\LoginPass\AbstractPasswordController
{

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
        $rep->body->assignZone('MAIN', 'passwordReset');

        return $rep;
    }

    /**
     * send an email to reset the password.
     */
    public function send()
    {
        $repError = $this->_check();
        if ($repError) {
            return $repError;
        }

        $rep = $this->getResponse('redirect');
        $rep->action = 'password_reset:index';

        $form = jForms::fill('password_reset');
        if (!$form) {
            return $this->badParameters();
        }
        if (!$form->check()) {
            return $rep;
        }

        $login = $form->getData('pass_login');
        $email = $form->getData('pass_email');

        /** @var Manager $manager */
        $manager = jAuthentication::manager()->getIdpById('loginpass')->getManager();
        $user = $manager->getUser($login);
        if (!$user || $user->getEmail() == '' || $user->getEmail() != $email) {
            \jLog::log('A password reset is attempted for unknown user "'.$login.'" and/or unknown email  "'.$email.'"', 'warning');
            // bad given email, ignore the error, so no change to discover
            // if a login is associated to an email or not
            jForms::destroy('password_reset');
            $rep->action = 'password_reset:sent';

            return $rep;
        }


        $passReset = new \Jelix\Authentication\LoginPass\PasswordReset(false, false, $manager);
        $result = $passReset->sendEmail($user);
        if ($result != $passReset::RESET_OK && $result != $passReset::RESET_BAD_LOGIN_EMAIL) {
            $form->setErrorOn('pass_login', jLocale::get('authloginpass~password.form.change.error.'.$result));
            return $rep;
        }

        jForms::destroy('password_reset');
        $rep->action = 'password_reset:sent';

        return $rep;
    }

    /**
     * Display the message that confirms the email sending
     *
     * @return jResponse|jResponseHtml|jResponseJson|jResponseRedirect|void
     * @throws Exception
     * @throws jExceptionSelector
     */
    public function sent() {
        $repError = $this->_check();
        if ($repError) {
            return $repError;
        }

        $rep = $this->_getLoginPassResponse(jLocale::get('password.form.title'), jLocale::get('password.page.title'));
        $tpl = new jTpl();
        $rep->body->assign('MAIN', $tpl->fetch('password_reset_waiting'));

        return $rep;
    }


    // see other actions into AbstractPasswordController

}
