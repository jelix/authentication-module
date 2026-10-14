<?php


use Jelix\Authentication\Core\Workflow\AccountWorkflowAdapterInterface;
use Jelix\Authentication\Core\Workflow\WorkflowAction;
use Jelix\Authentication\Core\Workflow\WorkflowState;

class AccountWorkflowAdapterForTest implements AccountWorkflowAdapterInterface
{

    protected $searchResult = 0;

    /**
     * @param int $searchResult one of AccountWorkflowAdapterInterface::SEARCH_RESULT_*
     * @return void
     */
    public function setSearchResult(int $searchResult)
    {
        $this->searchResult = $searchResult;
    }

    protected $accountToFind;

    public function setAccountToFind(?AccountForTest $account = null)
    {
        $this->accountToFind = $account;
        $this->searchResult = self::SEARCH_RESULT_ACCOUNT_FOUND;
    }

    public function searchAccount(WorkflowState $workflowState): int
    {
        switch ($this->searchResult) {
            case AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_FOUND:
                $user = $workflowState->getTemporaryUser();
                if ($this->accountToFind) {
                    $account = $this->accountToFind;
                }
                else {
                    $account = new AccountForTest(
                        $user->getUserId(),
                        $user->getLogin(),
                        $user->getName(),
                        $user->getEmail()
                    );
                }

                $user->setAccount($account);
                break;
        }
        return $this->searchResult;
    }

    protected $createAccountAction = null;

    public function setActionToCreateAccount(?WorkflowAction $createAccountAction = null)
    {
        $this->createAccountAction = $createAccountAction;
        if ($createAccountAction) {
            $this->searchResult = self::SEARCH_RESULT_ACCOUNT_TO_CREATE;
        }
        else {
            $this->createMissingAccount = 0;
        }
    }

    public function getActionToCreateAccount() : ?WorkflowAction
    {
        return $this->createAccountAction;
    }
}
