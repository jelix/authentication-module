<?php
/**
 * @author Laurent Jouanneau
 * @copyright  2026 Laurent Jouanneau
 * @license    MIT
 */
namespace Jelix\Authentication\Account;

use Jelix\Authentication\Account\Notification\AuthenticationNotifier;
use Jelix\Authentication\Core\Workflow\AccountWorkflowAdapterInterface;
use Jelix\Authentication\Core\Workflow\Step\StepException;
use Jelix\Authentication\Core\Workflow\WorkflowAction;
use Jelix\Authentication\Core\Workflow\WorkflowState;

class AccountWorkflowAdapterForWorkflow implements AccountWorkflowAdapterInterface
{

    protected Manager $accountManager;

    public function __construct()
    {
        $this->accountManager = new Manager();
    }

    public function searchAccount(WorkflowState $workflowState) : int
    {
        $result = AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_NOT_FOUND;
        $idpId = $workflowState->getIdpId();
        $authUser = $workflowState->getTemporaryUser();

        $isNewAccount = false;
        $account = $this->accountManager->searchAccountByIdp($idpId, $authUser->getIdpUserId(), true);
        if ($account) {

            $result = AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_FOUND;

        } else {

            $isAccountCreationAllowed = false;
            if (isset(\jApp::config()->accounts['autoCreateAccountOnLogin']) &&
                \jApp::config()->accounts['autoCreateAccountOnLogin']) {
                $isAccountCreationAllowed = true;
            }

            if ($isAccountCreationAllowed) {

                $isNewAccount = true;
                $account = $this->accountManager->createAccountFromAuthUser($authUser);

                $this->accountManager->attachAccountToIdp(
                    $account->getAccountId(),
                    $idpId,
                    $authUser->getUserId(),
                    $account->getEmail(),
                    $authUser->getAttribute('idpdata') ?? array(),
                    true);
                $result = AccountWorkflowAdapterInterface::SEARCH_RESULT_ACCOUNT_CREATED;
                /*
                $actions = [];
                // the url to redirect immediately, to fill the account form
                $formUrl = jUrl::get('account~createaccount:form',
                    array('uid'=>$authUser->getUserId()));

                // add this workflow action, by indicating the possible other action
                // after filling the form
                $actions[] = new WorkflowAction($formUrl, [
                    new \jSelectorActFast('classic', 'account', 'createaccount:save'),
                ]);


                $workflowState->setActions($actions);
                $result = AccountManagerInterface::SEARCH_RESULT_ACCOUNT_TO_CREATE;
                */
            }
        }

        if ($account) {

            $status = $account->getData('status');
            if ($status !== null && $status < Manager::STATUS_VALID) {
                throw new StepException(
                    \jLocale::get('account~account.error.account.disabled',
                        $authUser->getLogin())
                );
            }

            if (!$isNewAccount) {
                if ($workflowState->getWorkflowFeature(AccountWorkflowAdapterInterface::WORKFLOW_FEATURE_UPDATE_ACCOUNT_AT_LOGIN)) {
                    $this->accountManager->updateAccountFromAuthUser($authUser);
                }
                $this->accountManager->updateAccountIdpUsage(
                    $account->getAccountId(),
                    $idpId,
                    $authUser->getUserId(),
                    $authUser->getAttribute('idpdata')
                );
            }

            $authUser->setAccount($account);
            $notifier = new AuthenticationNotifier();
            $notifier->successAuth($account);
        }

        return $result;
    }

    public function getActionToCreateAccount() : ?WorkflowAction
    {
        return null;
    }
}
