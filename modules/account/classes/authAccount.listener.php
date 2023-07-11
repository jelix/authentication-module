<?php

use Jelix\Authentication\Account;
use Jelix\Authentication\Core\Workflow\Event\GetAccountEvent;

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
            $event->setUnknownAccount();
        }
    }

}
