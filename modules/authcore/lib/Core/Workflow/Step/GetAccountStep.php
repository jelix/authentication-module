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

class GetAccountStep extends AbstractStep
{

    protected $name = 'get_account';

    protected $transition = '';

    protected ?AccountWorkflowAdapterInterface $accountManager;

    public function __construct($eventDispatcher, WorkflowState $workflowState, ?AccountWorkflowAdapterInterface $accountManager = null)
    {
        $this->accountManager = $accountManager;
        parent::__construct($eventDispatcher, $workflowState);
    }

    /**
     * @return void
     */
    public function startStep($transition, WorkflowState $workflowState)
    {

        if (!$this->accountManager) {
            $this->transition = 'account_not_supported';
            return;
        }

        $result = $this->accountManager->searchAccount($workflowState);
        switch($result) {
            case AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_NOT_FOUND:
                throw new StepException('Account not found');
                break;
            case AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_FOUND:
                $this->transition = 'account_found';
                break;
            case AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_CREATED:
                $this->transition = 'account_found';
                $workflowState->newAccountCreated();
                break;
            case AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_TO_CREATE:
                $this->transition = 'account_to_create';
                break;
            case AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_NOT_SUPPORTED:
                $this->transition = 'account_not_supported';
                break;
        }
    }
}
