<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2022 Laurent Jouanneau
 * @link     http://jelix.org
 * @license MIT
 */

use Jelix\Authentication\Core\AuthSession\AuthSessionHandlerInterface;
use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\Workflow;

class phpAuthSessionHandler implements AuthSessionHandlerInterface
{

    const SESSION_NAME = 'JAUTH';

    /**
     * @param AuthUser $user
     * @param string $IPid
     */
    public function setSessionUser(AuthUser $user, $IPid)
    {
        $_SESSION[self::SESSION_NAME] = array(
            'user' => $user,
            'identProviderId' => $IPid
        );
    }

    public function unsetSessionUser()
    {
        if (isset($_SESSION[self::SESSION_NAME])) {
            unset($_SESSION[self::SESSION_NAME]);
        }
    }

    public function hasSessionUser()
    {
        return isset($_SESSION[self::SESSION_NAME]['user']);
    }

    /**
     * @return AuthUser|null
     */
    public function getSessionUser()
    {
        if (isset($_SESSION[self::SESSION_NAME]['user'])) {
            return $_SESSION[self::SESSION_NAME]['user'];
        }
        return null;
    }

    public function getIdentityProviderId()
    {
        if (isset($_SESSION[self::SESSION_NAME]['identProviderId'])) {
            return $_SESSION[self::SESSION_NAME]['identProviderId'];
        }
        return null;
    }

    public function setWorkflow(Workflow $workflow)
    {
        $_SESSION[self::SESSION_NAME]['workflow'] = $workflow;
    }

    public function unsetWorkflow()
    {
        if (isset($_SESSION[self::SESSION_NAME]['workflow'])) {
            unset($_SESSION[self::SESSION_NAME]['workflow']);
        }
    }

    public function getWorkflow()
    {
        if (isset($_SESSION[self::SESSION_NAME]['workflow'])) {
            return $_SESSION[self::SESSION_NAME]['workflow'];
        }
        return null;
    }
}
