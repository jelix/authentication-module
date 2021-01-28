<?php
/**
 * @author       Laurent Jouanneau <laurent@jelix.org>
 * @copyright    2018-2019 Laurent Jouanneau
 *
 * @link         http://jelix.org
 * @licence      http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
 */

namespace Jelix\Authentication\LoginPass;

use jAuthentication;
use Jelix\Authentication\Core\AuthSession\AuthUser;

class PasswordReset {

    protected $forRegistration = false;

    protected $byAdmin = false;

    protected $subjectLocaleId = '';

    protected $tplLocaleId = '';

    protected $manager;

    protected $config;

    function __construct($forRegistration = false, $byAdmin = false, $manager = null, $config = null) {
        $this->forRegistration = $forRegistration;
        $this->byAdmin = $byAdmin;
        if (!$manager) {
            $this->manager = jAuthentication::manager()->getIdpById('loginpass')->getManager();
        } else {
            $this->manager = $manager;
        }
        if (!$config) {
            $this->config = \jApp::config();
        } else {
            $this->config = $config;
        }
        if ($byAdmin) {
            $this->subjectLocaleId = 'authloginpass~mail.password.admin.reset.subject';
            $this->tplLocaleId = 'authloginpass~mail.password.admin.reset.body.html';
        }
        else {
            $this->subjectLocaleId = 'authloginpass~mail.password.reset.subject';
            $this->tplLocaleId = 'authloginpass~mail.password.reset.body.html';
        }
    }


    function sendEmail($login, $email)
    {
        $user = $this->manager->getUser($login);
        if (!$user || $user->getEmail() == '' || $user->getEmail() != $email) {
            \jLog::log('A password reset is attempted for unknown user "'.$login.'" and/or unknown email  "'.$email.'"', 'warning');
            return self::RESET_BAD_LOGIN_EMAIL;
        }

        if (!$this->manager->canChangePassword($user->getLogin())) {
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

        list($domain, $websiteUri) = $this->getWebInfos();

        $mail = $this->getMail($user->getEmail(), $domain);

        $tpl = new \jTpl();
        $tpl->assign('user', $user->getLogin());
        $tpl->assign('domain_name', $domain);
        $basePath = \jApp::urlBasePath();
        $tpl->assign('basePath', ($basePath == '/'?'':$basePath));
        $tpl->assign('website_uri', $websiteUri);
        $tpl->assign('confirmation_link', \jUrl::getFull(
            'authloginpass~password_reset:resetform@classic',
            array('login' => $user->getLogin(), 'key' => $key)
        ));
        $config = new Config();
        $tpl->assign('validationKeyTTL', $config->getValidationKeyTTLAsString());

        $body = $tpl->fetchFromString(\jLocale::get($this->tplLocaleId), 'html');
        $mail->msgHTML($body, '', array($mail, 'html2textKeepLinkSafe'));
        try {
            $mail->Send();
        }
        catch(\phpmailerException $e) {
            \jLog::logEx($e, 'error');
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

    /**
     * Configures the jMailer object to send the mail
     * 
     * @param string $email The email address
     * @param string $domain The domain name
     * @return \jMailer The mailer object
     */
    protected function getMail($email, $domain)
    {
        $mail = new \jMailer();
        $mail->From = $this->config->mailer['webmasterEmail'];
        $mail->FromName = $this->config->mailer['webmasterName'];
        $mail->Sender = $this->config->mailer['webmasterEmail'];
        $mail->Subject = \jLocale::get($this->subjectLocaleId, $domain);
        $mail->AddAddress($email);
        $mail->isHtml(true);
        return $mail;
    }

    protected function getWebInfos()
    {
        if (method_exists('jServer', 'getDomainName')) {
            $domain = \jServer::getDomainName();
            $websiteUri =  \jServer::getServerURI();
        }
        else {
            // old version of jelix < 1.7.5 && < 1.6.30
            $domain = \jApp::coord()->request->getDomainName();
            $websiteUri = \jApp::coord()->request->getServerURI();
        }

        return array($domain, $websiteUri);
    }

    function changePassword($user, $newPassword) {
        $user->setAttribute('status', AuthUser::STATUS_VALID);
        $user->setAttribute('keyactivate', '');
        $user->setAttribute('password', $newPassword);
        $this->manager->updateUser($user);
    }

}
