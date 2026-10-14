<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2026 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Step;

use Jelix\Authentication\Core\Workflow\AccountWorkflowAdapterInterface;
use Jelix\Authentication\Core\Workflow\WorkflowState;

class CreateAccountStep extends AbstractStep
{
    protected $name = 'create_account';

    protected $transition = 'account_created';

    protected ?AccountWorkflowAdapterInterface $accountManager;

    public function __construct($eventDispatcher, WorkflowState $workflowState, ?AccountWorkflowAdapterInterface $accountManager = null)
    {
        $this->accountManager = $accountManager;
        parent::__construct($eventDispatcher, $workflowState);
    }

    /**
     * start the step.
     *
     * @param string $transition the name of the transition that is applied to start the step
     * @return void
     */
    public function startStep($transition, WorkflowState $workflowState)
    {
        $this->workflowState->setActions([$this->accountManager->getActionToCreateAccount()]);
    }
}
