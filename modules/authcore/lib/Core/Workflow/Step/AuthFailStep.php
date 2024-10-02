<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Step;

use Jelix\Authentication\Core\Workflow\WorkflowState;

class AuthFailStep extends AbstractStep
{
    protected $name = 'auth_fail';

    protected $nextUrl = '';

    /**
     * @return void
     */
    public function startStep($transition, WorkflowState $workflowState)
    {
        $message = $workflowState->getErrorMessage();
        $workflowState->setEndStatus($workflowState::END_STATUS_FAIL, $message);
        $this->nextUrl = $workflowState->getFailUrl();

        \jMessage::add($message, 'error');
    }

    public function getNextActionUrl()
    {
        return $this->nextUrl;
    }
}