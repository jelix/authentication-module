<?php

use Jelix\Authentication\Account;

class createAccountListener extends jEventListener
{
    protected $eventMapping = array(
        // same listener for both AuthenticationLogin and AuthenticationUserCreation events
        'AuthenticationUserCreation' => 'onAuthenticationLogin'
    );

    function onAuthenticationLogin($event)
    {
        /** @var \Jelix\Authentication\Core\AuthSession\AuthUser $user */
        $user = $event->getParam('user');
        $provider = $event->getParam('identProvider');

        $name = $user->getName();
        if (Account\Manager::accountExists($name)) {
            return;
        }

        Account\Manager::createAccount($user, $provider);
    }
}
