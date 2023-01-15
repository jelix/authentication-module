<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license MIT
 */

namespace Jelix\Authentication\Core\AuthSession;

use Jelix\Authentication\Core\Workflow\WorkflowState;

interface AuthSessionHandlerInterface
{

    public function setSessionUser(AuthUser $user, $IPid);

    public function unsetSessionUser();

    public function hasSessionUser();

    /**
     * @return AuthUser|null
     */
    public function getSessionUser();

    public function getIdentityProviderId();

    public function setWorkflowState(WorkflowState $workflow);

    public function unsetWorkflowState();

    /**
     * @return WorkflowState
     */
    public function getWorkflowState();
}
