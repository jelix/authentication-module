<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2022 Laurent Jouanneau
 * @link     http://jelix.org
 * @license MIT
 */

namespace Jelix\Authentication\Core\AuthSession;

use Jelix\Authentication\Core\Workflow;

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

    public function setWorkflow(Workflow $workflow);

    public function unsetWorkflow();

    /**
     * @return Workflow
     */
    public function getWorkflow();
}
