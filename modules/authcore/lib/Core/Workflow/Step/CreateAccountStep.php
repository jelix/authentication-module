<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2026 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Step;

use Jelix\Authentication\Core\Workflow\Event\CreateAccountEvent;
use Jelix\Authentication\Core\Workflow\WorkflowState;

class CreateAccountStep extends AbstractStep
{
    protected $name = 'create_account';

    protected $transition = 'account_created';

    /**
     * start the step.
     *
     * @param string $transition the name of the transition that is applied to start the step
     * @return void
     */
    public function startStep($transition, WorkflowState $workflowState)
    {
        $event = new CreateAccountEvent($transition, $workflowState->getTemporaryUser(), $workflowState->getIdpId());
        $this->eventDispatcher->dispatch($event);
        $this->workflowState->setActions($event->getActions());
        $this->workflowState->newAccountCreated();
    }
}