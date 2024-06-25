<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2024 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Step;

use Jelix\Authentication\Core\Workflow\Event\CheckAccountEvent;
use Jelix\Authentication\Core\Workflow\WorkflowState;

class CheckAccountStep extends AbstractStep
{
    protected $name = 'check_account';

    protected $transition = 'account_checked';

    /**
     * start the step.
     *
     * @param string $transition the name of the transition that is applied to start the step
     * @return void
     */
    public function startStep($transition, WorkflowState $workflowState)
    {
        $event = new CheckAccountEvent($transition, $workflowState->getTemporaryUser(), $workflowState->getIdpId());
        $this->eventDispatcher->dispatch($event);
        $this->workflowState->setActions($event->getActions());
    }
}