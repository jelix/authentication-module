<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2024-2026 Laurent Jouanneau
 * @link     http://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Event;


use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\AuthSession\UserAccountInterface;

class CheckAccountEvent extends WorkflowStepEvent
{
    protected $accountNewlyCreated;

    public function __construct($transition, AuthUser $authenticatedUser, $idpId, $accountCreated = false)
    {
        $this->accountNewlyCreated = $accountCreated;
        parent::__construct('check_account', $transition, $authenticatedUser, $idpId);
    }

    /**
     * @return UserAccountInterface
     */
    public function getAccount()
    {
        return $this->getUserBeingAuthenticated()->getAccount();
    }

    /**
     * Should be called by listeners that modified the account and want to
     * propagate the updated account into the workflow and the session.
     *
     * @param UserAccountInterface $account
     * @return void
     */
    public function setAccount(UserAccountInterface $account)
    {
        $this->getUserBeingAuthenticated()->setAccount($account);
    }

    public function isAccountNewlyCreated()
    {
        return $this->accountNewlyCreated;
    }
}
