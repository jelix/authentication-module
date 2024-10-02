<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license MIT
 */

use Jelix\Authentication\Core\AuthSession\AuthSessionHandlerInterface;
use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\Workflow\WorkflowState;

class varAuthSessionHandler implements AuthSessionHandlerInterface
{

    /**
     * @var AuthUser|null
     */
    protected $user = null;

    /**
     * @var string|null
     */
    protected $identProviderId = null;

    /**
     * @var WorkflowState
     */
    protected $workflow;

    /**
     * @param AuthUser $user
     * @param string $IPid
     */
    public function setSessionUser(AuthUser $user, $IdpId)
    {
        $this->user = $user;
        $this->identProviderId = $IdpId;
        $this->workflow = null;
    }

    public function unsetSessionUser()
    {
        $this->user = null;
        $this->identProviderId = null;
    }

    public function hasSessionUser()
    {
        return $this->user !== null;
    }

    /**
     * @return AuthUser|null
     */
    public function getSessionUser()
    {
        return $this->user;
    }

    public function getIdentityProviderId()
    {
        return $this->identProviderId;
    }

    public function setWorkflowState(WorkflowState $workflow)
    {
        $this->workflow = $workflow;
    }

    public function unsetWorkflowState()
    {
        $this->workflow = null;
    }

    public function getWorkflowState()
    {
        return $this->workflow;
    }
}
