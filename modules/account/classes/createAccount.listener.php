<?php

use Jelix\Authentication\Account;

class createAccountListener extends jEventListener
{
    protected $eventMapping = array(
        'AuthenticationLogin' => 'onAuthenticationUserCreation'
    );

    function onAuthenticationUserCreation($event)
    {
        $user = $event->getParam('user');
        $provider = $event->getParam('identProviderId');

        $name = $user->getName();
        if (Account\Manager::accountExists($name)) {
            return false;
        }

        Account\Manager::createAccount($user, $provider);
    }
}