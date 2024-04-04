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

use Jelix\Authentication\LoginPass\PasswordReset;
use Jelix\Authentication\Core\AuthSession\AuthUser;

/**
 * controller for the password reset process, initiated by an admin
 */
class password_reset_adminCtrl extends \Jelix\Authentication\LoginPass\AbstractController
{

    public $pluginParams = array(
        '*' => array('auth.required' => true)
    );

    protected function _checkadmin()
    {
        if (!$this->config->isResetAdminPasswordEnabledForAdmin()) {
            return $this->notavailable();
        }
        return null;
    }


    protected function _checkUser($login, $rep)
    {
        /** @var \Jelix\Authentication\LoginPass\Manager $manager */
        $manager = jAuthentication::manager()->getIdpById('loginpass')->getManager();
        $user = $manager->getUser($login);
        if (!$user || $user->getEmail() == '') {
            $this->showError($rep, 'no_access_wronguser');
            return false;
        }

        if (!$manager->canChangePassword($login)) {
            $this->showError($rep, 'no_access_badstatus');
            return false;
        }

        return $user;
    }

    /**
     * form to confirm the password reset
     */
    public function index()
    {
        $repError = $this->_checkadmin();
        if ($repError) {
            return $repError;
        }

        $rep = $this->_getLoginPassResponse(jLocale::get('password.form.title'));

        $login = $this->param('login');
        $user = $this->_checkUser($login, $rep);
        if ($user === false) {
            return $rep;
        }

        $tpl = new jTpl();
        $tpl->assign('login', $login);
        $rep->body->assign('MAIN', $tpl->fetch('password_reset_admin'));

        return $rep;
    }

    /**
     * send an email to reset the password.
     */
    public function send()
    {
        $repError = $this->_checkadmin();
        if ($repError) {
            return $repError;
        }

        $login = $this->param('pass_login');
        $rep = $this->_getLoginPassResponse(jLocale::get('password.form.title'));
        $user = $this->_checkUser($login, $rep);
        if ($user === false) {
            return $rep;
        }

        $rep = $this->getResponse('redirect');
        $rep->action = 'password_reset_admin:index';

        $status = $user->getAttribute('status');
        if ($status == AuthUser::STATUS_VALID ||
            $status == AuthUser::STATUS_PWD_CHANGED
        ) {
            $passReset = new PasswordReset(true, true);
            $result = $passReset->sendEmail($user);
        }
        else {
            $result = PasswordReset::RESET_BAD_STATUS;
        }

        if ($result != PasswordReset::RESET_OK) {
            $rep = $this->_getLoginPassResponse(jLocale::get('password.form.title'));

            $tpl = new \jTpl();
            $tpl->assign('login', $login);
            $tpl->assign('error_status', $result);
            $rep->body->assign('MAIN', $tpl->fetch('password_reset_admin_error'));
            return $rep;
        }

        $rep->action = 'password_reset_admin:sent';
        $rep->params = array('login'=>$login);

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
        $repError = $this->_checkadmin();
        if ($repError) {
            return $repError;
        }

        $rep = $this->_getLoginPassResponse(jLocale::get('password.form.title'));
        $tpl = new jTpl();
        $tpl->assign('login', $this->param('login'));
        $rep->body->assign('MAIN', $tpl->fetch('password_reset_admin_waiting'));

        return $rep;
    }
}