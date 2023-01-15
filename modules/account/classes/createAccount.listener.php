<?php

use Jelix\Authentication\Account;
use Jelix\Authentication\Core\IdentityProviderInterface;

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

        /** @var IdentityProviderInterface */
        $provider = $event->getParam('identProvider');

        $name = $user->getName();
        if (Account\Manager::accountExists($name)) {
            return;
        }

        Account\Manager::createAccount($user, $provider);
    }

    function onAuthenticationCanUseApp($event)
    {
        /** @var \Jelix\Authentication\Core\AuthSession\AuthUser $user */
        $user = $event->getParam('user');

        /** @var IdentityProviderInterface */
        $provider = $event->getParam('identProvider');

        // est-ce qu'il y a un compte rattaché à cet idp ?
        // 

    }
}
