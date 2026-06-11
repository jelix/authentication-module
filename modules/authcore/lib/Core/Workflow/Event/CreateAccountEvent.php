<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2026 Laurent Jouanneau
 * @link     http://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Event;


use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\AuthSession\UserAccountInterface;

class CreateAccountEvent extends WorkflowStepEvent
{
    public function __construct($transition, AuthUser $authenticatedUser, $idpId)
    {
        parent::__construct('create_account', $transition, $authenticatedUser, $idpId);
    }

    public function setAccount(UserAccountInterface $account)
    {
        $this->getUserBeingAuthenticated()->setAccount($account);
    }

}
