<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2023 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core\AuthSession;

use Jelix\Authentication\Core\IdentityProviderInterface;
use Jelix\Authentication\Core\Workflow\WorkflowState;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Manage the authenticated session
 *
 */
class AuthSession
{

    /**
     * @var AuthSessionHandlerInterface
     */
    protected $handler;

    /**
     * @var EventDispatcherInterface
     */
    protected $evDispatcher;

    public function __construct(AuthSessionHandlerInterface $handler, EventDispatcherInterface $evDispatcher)
    {
        $this->handler = $handler;
        $this->evDispatcher = $evDispatcher;
    }

    /**
     * @param AuthUser $user
     * @param IdentityProviderInterface $idp
     * @return bool false if the authenticated user is not allowed to use the application
     */
    public function setSessionUser(AuthUser $user, $idp)
    {
        $event = new \jEvent('AuthenticationCanUseApp', array(
            'user' => $user,
            'identProvider' => $idp
        ));
        $event = $this->evDispatcher->dispatch($event);
        if (false === $event->allResponsesByKeyAreTrue('canUseApp')) {
            return false;
        }

        $this->handler->setSessionUser($user, $idp->getId());

        $event = new \jEvent('AuthenticationLogin', array(
            'user' => $user,
            'identProvider' => $idp
        ));
        $this->evDispatcher->dispatch($event);

        return true;
    }

    public function unsetSessionUser()
    {
        if ($this->handler->hasSessionUser()) {
            $user = $this->handler->getSessionUser();
            $this->handler->unsetSessionUser();
            $event = new \jEvent('AuthenticationLogout', array(
                'user' => $user,
            ));
            $this->evDispatcher->dispatch($event);
        } else {
            $this->handler->unsetSessionUser();
        }
    }

    public function hasSessionUser()
    {
        return $this->handler->hasSessionUser();
    }

    /**
     * @return AuthUser|null
     */
    public function getSessionUser()
    {
        return $this->handler->getSessionUser();
    }

    public function getIdentityProviderId()
    {
        return $this->handler->getIdentityProviderId();
    }

    /**
     * @param WorkflowState $workflow
     * @return void
     */
    public function setWorkflowState(WorkflowState $workflow)
    {
        $this->handler->setWorkflowState($workflow);
    }

    public function unsetWorkflowState()
    {
        $this->handler->unsetWorkflowState();
    }

    /**
     * @return WorkflowState
     */
    public function getWorkflowState()
    {
        return $this->handler->getWorkflowState();
    }
}
