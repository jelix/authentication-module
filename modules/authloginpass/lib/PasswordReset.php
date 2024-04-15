<?php
/**
 * @author    Laurent Jouanneau <laurent@jelix.org>
 * @copyright 2018-2024 Laurent Jouanneau
 *
 * @link      https://jelix.org
 * @licence   MIT
 */

namespace Jelix\Authentication\LoginPass;

use jAuthentication;
use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\RequestConfirmation\Requests;
use Jelix\Authentication\RequestConfirmation\UserRequest;
use Psr\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    protected $eventManager;

    /**
     * @param bool $forRegistration
     * @param Manager $manager
     * @param Config $config
     * @param EventDispatcherInterface $eventManager
     */
    function __construct($forRegistration = false, $manager = null, $config = null, $eventManager = null)
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

        $this->eventManager = $eventManager;
    }

    public function isPasswordResetEnabled()
    {
        if (!$this->config->isResetPasswordEnabled()) {
            return false;
        }

        foreach($this->manager->getBackends() as $backend)
        {
            if ($backend->getFeatures() & $backend::FEATURE_CHANGE_PASSWORD) {
                return true;
            }
        }
        return false;
    }

    public function findUser($email)
    {
        return $this->manager->searchUserHavingEmail($email);
    }


    /**
     * @param AuthUser|null $user
     * @return string the request Id
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws PasswordResetException
     */
    function sendEmail($user)
    {
        if (!$user) {
            \jLog::log('A password reset is attempted for unknown user', 'warning');
            throw new PasswordResetException(PasswordResetException::CODE_BAD_LOGIN_EMAIL);
        }

        $login = $user->getLogin();
        $email = $user->getEmail();
        if ($email == '') {
            \jLog::log('A password reset is attempted for the user "'.$login.'" having no mail', 'warning');
            throw new PasswordResetException(PasswordResetException::CODE_BAD_LOGIN_EMAIL);
        }

        if (!$this->manager->canChangePassword($login)) {
            throw new PasswordResetException(PasswordResetException::CODE_BAD_STATUS);
        }

        $status = $user->getAttribute('status');
        if ($status != AuthUser::STATUS_VALID &&
            $status != AuthUser::STATUS_NEW
        ) {
            throw new PasswordResetException(PasswordResetException::CODE_BAD_STATUS);
        }

        // check if a component does not want to reset the password
        if ($this->eventManager) {
            $event = new \Jelix\Authentication\LoginPass\AuthLPCanResetPasswordEvent('loginpass', $user);
            $this->eventManager->dispatch($event);
            if (!$event->isResetPasswordAllowed()) {
                throw new PasswordResetException(PasswordResetException::CODE_BAD_STATUS);
            }
        }

        $request = new Requests();

        $req = $request->createRequest(
            UserRequest::TYPE_RECOVERY_ACCOUNT,
            $login,
            $email,
            $this->config->getValidationKeyTTL()
        );

        $tpl = new \jTpl();
        $tpl->assign('login', $login);
        $tpl->assign('appName', $this->config->getApplicationName());
        $tpl->assign('code', $req->getReadableRequestCode());
        $tpl->assign('validationKeyTTL', $this->config->getValidationKeyTTLAsString());

        if (!$this->config->sendHtmlEmail(
            $email,
            \jLocale::get($this->subjectLocaleId, $this->config->getApplicationName()),
            $tpl,
            \jLocale::get($this->tplLocaleId))
        ) {
            throw new PasswordResetException(PasswordResetException::CODE_MAIL_SERVER_ERROR);
        }

        return $req->getRequestId();
    }

    /**
     * @return string a random request id
     */
    function sendFakeEmail()
    {
        $request = new Requests();

        $req = $request->createCancelledRequest(
            UserRequest::TYPE_RECOVERY_ACCOUNT
        );

        return $req->getRequestId();
    }

    /**
     * @param string $requestId
     * @param string $key
     * @return AuthUser|string An error code or the user object if the key is ok
     * @throws \Exception
     */
    function checkKey($requestId, $code)
    {
        if ($requestId == '' || $code == '') {
            throw new PasswordResetException(PasswordResetException::CODE_BAD_CONFIRMATION_CODE);
        }

        $request = new Requests();

        $userRequest = $request->checkRequest($requestId, $code);

        $login = $userRequest->getLogin();
        $user = $this->manager->getUser($login);
        if (!$user) {
            throw new PasswordResetException(PasswordResetException::CODE_BAD_CONFIRMATION_CODE);
        }

        if (!$this->manager->canChangePassword($login)) {
            throw new PasswordResetException(PasswordResetException::CODE_BAD_STATUS);
        }

        $userRequest->saveAsChecked();

        return $user;
    }


    function isConfirmationCodeChecked($requestId)
    {
        $request = new Requests();
        $userRequest = $request->getRequest($requestId);
        if ($userRequest && $userRequest->hasStatus($userRequest::STATUS_CHECKED)) {
            return $userRequest;
        }
        return false;
    }


    function changePassword(UserRequest $userRequest, $newPassword)
    {
        $login = $userRequest->getLogin();
        $backend = $this->manager->getBackendHavingUser($login);
        if ($backend) {
            $userRequest->saveAsConfirmed();

            $this->manager->changePassword($login, $newPassword, $backend->getLabel());
        }
    }

}
