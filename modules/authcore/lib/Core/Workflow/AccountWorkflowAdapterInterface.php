<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2026 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core\Workflow;

/**
 * Interface for object managing accounts and used during the workflow authentication
 *
 * The object should be implemented by modules managing accounts. The class
 * should be declared into the `accountWorkflowAdapter` configuration parameter (section `authentication`)
 */
interface AccountWorkflowAdapterInterface
{

    //public function getAccountById(string $accountId): ?UserAccountInterface;

    /**
     * result of searchAccount(): no account corresponding to the authenticated user
     * It will result as an authentication failure
     */
    const SEARCH_RESULT_ACCOUNT_NOT_FOUND = 0;

    /**
     * result of searchAccount(): an account corresponding to the authenticated user has been found
     */
    const SEARCH_RESULT_ACCOUNT_FOUND = 1;

    /**
     * result of searchAccount(): an account corresponding to the authenticated user has been created directly
     */
    const SEARCH_RESULT_ACCOUNT_CREATED = 3;

    /**
     * result of searchAccount(): an account corresponding to the authenticated user should be created and
     * needs user inputs
     */
    const SEARCH_RESULT_ACCOUNT_TO_CREATE = 4;

    /**
     * Result of searchAccount(): account management is not supported.
     * It will result as an authentication success
     */
    const SEARCH_RESULT_ACCOUNT_NOT_SUPPORTED = 5;

    /**
     * Search an account for the temporary user indicated into the workflow state.
     *
     * If an account is found or created, it should be set on the temporary user object.
     * If the user should be redirected to some pages (like a form to create an account),
     * the WorkflowState object should be used to indicate the actions (see `setActions()`).
     *
     * @param WorkflowState $workflowState
     * @return int one of the SEARCH_RESULT_* constants
     */
    public function searchAccount(WorkflowState $workflowState) : int;

    /**
     * When an account has to be created with some controllers (searchAccount has returned SEARCH_RESULT_ACCOUNT_TO_CREATE),
     * this method should return all the action involved into the account creation
     *
     * @return WorkflowAction
     */
    public function getActionToCreateAccount() : ?WorkflowAction;

}
