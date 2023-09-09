<?php

use Jelix\Authentication\Account;
use Jelix\Authentication\Core\Workflow\Event\GetAccountEvent;
use Jelix\Authentication\Core\Workflow\Step\StepException;

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

        $account = Account\Manager::searchAccountByIdp($idpId, $user->getUserId());
        if ($account) {
            $event->setAccount($account);
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
                throw new StepException('No account for the login '.$user->getLogin());
            }
        }
    }

}
