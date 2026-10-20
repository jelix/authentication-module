<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2023-2026 Laurent Jouanneau
 *
 * @see        https://jelix.org
 * @licence     MIT
 */
use Jelix\Authentication\Account;
use Jelix\Authentication\LoginPass\LoginPassCanResetPasswordEvent;

class authAccountListener extends jEventListener
{
    protected $eventMapping = array(
    );

    /**
     * @param LoginPassCanResetPasswordEvent $event
     * @return void
     */
    function onLoginPassCanResetPassword($event)
    {
        if (!($event instanceof LoginPassCanResetPasswordEvent)) {
            return;
        }

        $userId = $event->getUser()->getIdpUserId();

        // if a user has no account, we don't allow him to recover his account
        // and so, to reset his password.
        $manager = new Account\Manager();
        $account = $manager->searchAccountByIdp($event->getIdpId(), $userId);
        if ($account) {
            $event->allow();
        }
        else {
            $event->deny();
        }
    }
}
