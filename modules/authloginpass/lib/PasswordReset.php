<?php
/**
 * @author       Laurent Jouanneau <laurent@jelix.org>
 * @copyright    2018-2023 Laurent Jouanneau
 *
 * @link         http://jelix.org
 * @licence      http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */

namespace Jelix\Authentication\LoginPass;

use jAuthentication;
use Jelix\Authentication\Core\AuthSession\AuthUser;

class PasswordReset {

    protected $forRegistration = false;

    protected $subjectLocaleId = 'authloginpass~mail.password.resetcode.subject';

    protected $tplLocaleId = 'authloginpass~mail.password.resetcode.body.html';

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param bool $forRegistration
     * @param Manager $manager
     * @param Config $config
     */
    function __construct($forRegistration = false, $manager = null, $config = null)
    {
        $this->forRegistration = $forRegistration;
        if (!$manager) {
            $this->manager = jAuthentication::manager()->getIdpById('loginpass')->getManager();
        } else {
            $this->manager = $manager;
        }
        if ($config) {
            $this->config = $config;
        } else {
            $this->config =  new Config(\jApp::config());
        }
    }

    /**
     * @param AuthUser|null $user
     * @return string
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \jException
     * @throws \jExceptionSelector
     */
    function sendEmail($user)
    {
        if (!$user) {
            \jLog::log('A password reset is attempted for unknown user', 'warning');
            return self::RESET_BAD_LOGIN_EMAIL;
        }

        $login = $user->getLogin();
        $email = $user->getEmail();
        if ($email == '') {
            \jLog::log('A password reset is attempted for the user "'.$login.'" having no mail', 'warning');
            return self::RESET_BAD_LOGIN_EMAIL;
        }

        if (!$this->manager->canChangePassword($login)) {
            return self::RESET_BAD_STATUS;
        }

        $status = $user->getAttribute('status');
        if ($status != AuthUser::STATUS_VALID &&
            $status != AuthUser::STATUS_PWD_CHANGED &&
            $status != AuthUser::STATUS_NEW
        ) {
            return self::RESET_BAD_STATUS;
        }

        $key = sha1(password_hash($login.$email.microtime(),PASSWORD_DEFAULT));
        if ($status != AuthUser::STATUS_NEW) {
            $status = AuthUser::STATUS_PWD_CHANGED;
        }
        $user->setAttribute('request_date', date('Y-m-d H:i:s'));
        $user->setAttribute('keyactivate', ($this->byAdmin?'A:':'U:').$key);
        $user->setAttribute('status', $status);

        $config = new Config();
        list($domain, $websiteUri) = $config->getDomainAndServerURI();


        $tpl = new \jTpl();
        $tpl->assign('user', $user->getLogin());
        $tpl->assign('confirmation_link', \jUrl::getFull(
            'authloginpass~password_reset:resetform@classic',
            array('login' => $user->getLogin(), 'key' => $key)
        ));
        $tpl->assign('validationKeyTTL', $config->getValidationKeyTTLAsString());

        if (!$config->sendHtmlEmail(
            $email,
            \jLocale::get($this->subjectLocaleId, $domain),
            $tpl,
            \jLocale::get($this->tplLocaleId))
        ) {
            return self::RESET_MAIL_SERVER_ERROR;
        }

        $this->manager->updateUser($user);

        return self::RESET_OK;
    }

    const RESET_BAD_LOGIN_EMAIL = "badloginemail";

    const RESET_ALREADY_DONE = "alreadydone";
    const RESET_OK = "ok";
    const RESET_BAD_KEY = "badkey";
    const RESET_EXPIRED_KEY = "expiredkey";
    const RESET_BAD_STATUS = "badstatus";
    const RESET_MAIL_SERVER_ERROR = "smtperror";

    /**
     * @param string $login
     * @param string $key
     * @return object|string
     * @throws \Exception
     */
    function checkKey($login, $key)
    {
        if ($login == '' || $key == '') {
            return self::RESET_BAD_KEY;
        }
        $user = $this->manager->getUser($login);
        if (!$user) {
            return self::RESET_BAD_KEY;
        }
        $keyactivate = $user->getAttribute('keyactivate');
        $request_date = $user->getAttribute('request_date');
        if ($keyactivate == '' ||
            $request_date == ''
        ) {
            return self::RESET_BAD_KEY;
        }

        if (preg_match('/^([AU]:)(.+)$/', $keyactivate , $m)) {
            $keyactivate = $m[2];
        }

        if ($keyactivate != $key) {
            return self::RESET_BAD_KEY;
        }
        
        $status = $user->getAttribute('status');
        $expectedStatus = ($this->forRegistration? AuthUser::STATUS_NEW : AuthUser::STATUS_PWD_CHANGED);
        if ($status != $expectedStatus) {
            if ($status == AuthUser::STATUS_VALID) {
                return self::RESET_ALREADY_DONE;
            }
            return self::RESET_BAD_STATUS;
        }

        if (!$this->manager->canChangePassword($login)) {
            return self::RESET_BAD_STATUS;
        }

        $config = new Config($this->config);
        $dt = new \DateTime($request_date);
        $dtNow = new \DateTime();
        $dt->add($config->getValidationKeyTTL());
        if ($dt < $dtNow ) {
            return self::RESET_EXPIRED_KEY;
        }
        return $user;
    }

    function changePassword($user, $newPassword)
    {
        $user->setAttribute('status', AuthUser::STATUS_VALID);
        $user->setAttribute('keyactivate', '');
        $user->setAttribute('password', $newPassword);
        $this->manager->updateUser($user);
    }

}
