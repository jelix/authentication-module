<?php

use Jelix\Authentication\Core\IdentityProviderInterface;

class AuthManagerForTest implements IdentityProviderInterface
{

    public function __construct(array $options)
    {
    }

    public function getId()
    {
        return 'baridp';
    }

    public function getLoginUrl()
    {
        return '';
    }

    public function getLogoutUrl()
    {
        return '';
    }

    public function getHtmlLoginForm(\jRequest $request)
    {
        return '';
    }

    public function checkSessionValidity($request, $authUser, $authRequired)
    {
        return null;
    }
}
