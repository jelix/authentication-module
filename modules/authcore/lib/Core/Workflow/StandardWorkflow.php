<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow;

use Jelix\Authentication\Core\AuthenticatorManager;
use Jelix\Authentication\Core\AuthSession\AuthSession;
use Psr\EventDispatcher\EventDispatcherInterface;

class StandardWorkflow
{

    /**
     * @var Workflow
     */
    protected $workflow;

    /**
     * @var EventDispatcherInterface
     */
    protected $evDispatcher;

    /**
     * @var AuthSession
     */
    protected $session;


    /**
     * @param EventDispatcherInterface $evDispatcher
     */
    function __construct(
        AuthSession $sessionHandler,
        EventDispatcherInterface $evDispatcher
    )
    {
        $this->session = $sessionHandler;
        $this->evDispatcher = $evDispatcher;
    }

    /**
     * @param WorkflowState $workflowState
     * @return Workflow
     */
    protected function getWorkflowInstance($workflowState)
    {
        $evDispatcher = $this->evDispatcher;
        $workflow = new Workflow($workflowState);

        $steps = array (
            new Step\GetAccountStep($evDispatcher, $workflowState),
            new Step\CreateAccountStep($evDispatcher, $workflowState),
            new Step\CheckAccountStep($evDispatcher, $workflowState),
            new Step\SecondFactorAuthStep($evDispatcher, $workflowState),
            new Step\AccessValidationStep($evDispatcher, $workflowState),
            new Step\AuthDoneStep($evDispatcher, $workflowState),
            new Step\AuthFailStep($evDispatcher, $workflowState)
        );

        $transitions = array(
            'account_found' => array(
                'from' => 'get_account',
                'to' => 'check_account'
            ),
            'account_not_found' => array(
                'from' => 'get_account',
                'to' => 'create_account'
            ),
            'account_not_supported' => array(
                'from' => 'get_account',
                'to' => 'access_validation'
            ),
            'account_created' => array(
                'from' => 'create_account',
                'to' => 'access_validation'
            ),
            'account_checked' => array(
                'from' => 'check_account',
                'to' => 'second_factor'
            ),
            'second_factor_success' => array(
                'from' => 'second_factor',
                'to' => 'access_validation'
            ),
            'validation' => array(
                'from' => 'access_validation',
                'to' => 'auth_done'
            ),
            'fail' => array(
                'from' => ['get_account', 'create_account', 'check_account', 'second_factor', 'access_validation', ],
                'to' => 'auth_fail'
            )
        );

        $workflow->setup($steps, $transitions, 'get_account');
        return $workflow;
    }


    public function start($workflowState)
    {
        $this->session->setWorkflowState($workflowState);
        $this->workflow = $this->getWorkflowInstance($workflowState);
        $this->workflow->apply('start');
        return $this->workflow;
    }

    public function stop()
    {
        $this->session->unsetWorkflowState();
        $this->workflow = null;
    }

    /**
     * @return Workflow|null
     */
    public function getWorkflow()
    {
        $workflowState = $this->session->getWorkflowState();
        if (!$workflowState) {
            return null;
        }

        return $this->getWorkflowInstance($workflowState);
    }

    /**
     * @param $isLogonned
     * @param \jIActionSelector $action
     * @param AuthenticatorManager $authManager
     * @return \jIActionSelector|null
     */
    public function checkWorkflowAndAction(&$isLogonned, \jIActionSelector $action, AuthenticatorManager $authManager)
    {
        $workflow = $this->getWorkflow();
        if (!$workflow) {
            return null;
        }

        $selector = null;
        if ($isLogonned) {
            // the user is authenticated, so we must destroy the authentication workflow,
            // as it is not relevant anymore
            $this->stop();
        } else if ($workflow->isFinished()) {
            if ($workflow->isSuccess()) {
                $isLogonned = $this->session->setSessionUser(
                    $workflow->getTemporaryUser(),
                    $authManager->getIdpById($workflow->getIdpId())
                );
            }
            $this->stop();
        } else {
            // the user is not authenticated.
            // Let's force to use the action of the current step of the workflow
            $step = $workflow->getCurrentStep();
            $selector = $step->getExpectedAction($action);
        }

        return $selector;
    }
}
