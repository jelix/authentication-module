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
     * @var Workflow
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

    public function setWorkflow(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function unsetWorkflow()
    {
        $this->workflow = null;
    }

    public function getWorkflow()
    {
        return $this->workflow;
    }
}
