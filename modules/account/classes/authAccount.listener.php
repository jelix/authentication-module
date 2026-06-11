<?php

use Jelix\Authentication\Account;
use Jelix\Authentication\Account\Notification\AuthenticationNotifier;
use Jelix\Authentication\Core\Workflow\Event\GetAccountEvent;
use Jelix\Authentication\Core\Workflow\Step\StepException;
use Jelix\Authentication\LoginPass\LoginPassCanResetPasswordEvent;

class authAccountListener extends jEventListener
{
    protected $eventMapping = array(
    );

    /**
     * @param GetAccountEvent $event
     * @return void
     */
    function onAuthWorkflowStep($event)
    {
        if (!($event instanceof GetAccountEvent)) {
            return;
        }

        /** @var \Jelix\Authentication\Core\AuthSession\AuthUser $user */
        $user = $event->getUserBeingAuthenticated();
        $idpId = $event->getIdpId();

        $account = Account\Manager::searchAccountByIdp($idpId, $user->getUserId(), true);
        if ($account) {
            $event->setAccount($account);
            $notifier = new AuthenticationNotifier();
            $notifier->successAuth($account);
        }
        else {
            $isAccountCreationAllowed = false;
            if (isset(\jApp::config()->accounts['autoCreateAccountOnLogin']) &&
                \jApp::config()->accounts['autoCreateAccountOnLogin']) {
                $isAccountCreationAllowed = true;
            }
            if ($isAccountCreationAllowed) {
                $event->setUnknownAccount();
            }
            else {
                throw new StepException(jLocale::get('account~account.error.no.account', $user->getLogin()));
            }
        }
    }

    /**
     * @param LoginPassCanResetPasswordEvent $event
     * @return void
     */
    function onLoginPassCanResetPassword($event)
    {
        if (!($event instanceof LoginPassCanResetPasswordEvent)) {
            return;
        }

        $userId = $event->getUser()->getUserId();

        // if a user has no account, we don't allow him to recover his account
        // and so, to reset his password.
        $account = Account\Manager::searchAccountByIdp($event->getIdpId(), $userId);
        if ($account) {
            $event->allow();
        }
        else {
            $event->deny();
        }
    }
}
