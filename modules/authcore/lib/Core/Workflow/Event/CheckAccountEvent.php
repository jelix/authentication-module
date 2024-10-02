<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2024 Laurent Jouanneau
 * @link     http://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Event;


use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\AuthSession\UserAccountInterface;

class CheckAccountEvent extends WorkflowStepEvent
{
    public function __construct($transition, AuthUser $authenticatedUser, $idpId)
    {
        parent::__construct('check_account', $transition, $authenticatedUser, $idpId);
    }

    /**
     * @return UserAccountInterface
     */
    public function getAccount()
    {
        return $this->getUserBeingAuthenticated()->getAccount();
    }

}
