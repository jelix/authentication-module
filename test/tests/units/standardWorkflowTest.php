<?php

use Jelix\Authentication\Core\Workflow\AccountWorkflowAdapterInterface;
use PHPUnit\Framework\TestCase;
use Jelix\Authentication\Core\Workflow;
use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\AuthSession\AuthSession;
use Jelix\Authentication\Core\AuthenticatorManager;


require_once (__DIR__.'/../../../modules/authcore/plugins/authsession/var/var.authsession.php');


class standardWorkflowTest extends TestCase
{

    /**
     * @var Workflow\StandardWorkflow
     */
    protected $standardWorkflow;

    /**
     * @var Workflow\WorkflowState
     */
    protected $workflowState;

    /**
     * @var EventDispatcherForTests
     */
    protected $evDispatcher;

    /**
     * @var AccountWorkflowAdapterInterface
     */
    protected $accountManager;

    public function setUp(): void
    {
        $varSessionHandler = new varAuthSessionHandler();
        $this->evDispatcher = new EventDispatcherForTests();
        $this->accountManager = new AccountWorkflowAdapterForTest();

        $session = new AuthSession($varSessionHandler, $this->evDispatcher);
        $this->standardWorkflow = new Workflow\StandardWorkflow($session, $this->evDispatcher, $this->accountManager);
        $user = new AuthUser('123', array(
            AuthUser::ATTR_LOGIN => 'bob',
            AuthUser::ATTR_NAME => 'Bob Morane',
            AuthUser::ATTR_EMAIL => 'bob@example.com',
        ));
        $this->workflowState = new Workflow\WorkflowState($user, 'baridp');
    }

    public function testEmptyWorkflowSuccess()
    {
        $this->accountManager->setSearchResult(AccountWorkflowAdapterForTest::SEARCH_RESULT_ACCOUNT_NOT_SUPPORTED);

        // --- start action
        $workflow = $this->standardWorkflow->start($this->workflowState);
        $workflow->setFinalUrl('http://localhost/home');

        // --- mimic redirection
        $redirectUrl = $workflow->getNextAuthenticationUrl();
        $this->assertEquals('http://localhost/home', $redirectUrl);

        $this->assertTrue($workflow->isFinished());
        $this->assertTrue($workflow->isSuccess());

        // --- mimic what the coord plugin does
        $isAuthenticated = false;
        $currentAction = new \jSelectorDebugAction('classic', 'testapp', 'default:home');
        $manager = new AuthenticatorManager([ new AuthManagerForTest([]) ]);
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $currentAction, $manager);
        $this->assertNull($checkResult);

        // end
        $this->assertNull($this->standardWorkflow->getWorkflow());
        $this->assertTrue($isAuthenticated);
        $this->assertEquals($this->workflowState::END_STATUS_SUCCESS, $this->workflowState->getEndStatus());

    }

    public function testOneStep()
    {
        $this->accountManager->setSearchResult(AccountWorkflowAdapterForTest::SEARCH_RESULT_ACCOUNT_NOT_SUPPORTED);

        $manager = new AuthenticatorManager([ new AuthManagerForTest([]) ]);

        // --- setup step actions
        $homeAction  = new \jSelectorDebugAction('classic', 'testapp', 'default:home');
        $sfAction = new \jSelectorDebugAction('classic', 'testapp', 'secondfactor:index');
        $this->evDispatcher->addWorkflowActionForStep(
            'access_validation',
            new Workflow\WorkflowAction('/url-access-validation', [
                $sfAction
            ])
        );

        // --- start action: the action that authenticated the user initialize the workflow
        $workflow = $this->standardWorkflow->start($this->workflowState);
        $workflow->setFinalUrl('http://localhost/home');

        // --- then it redirects to the next url
        $redirectUrl = $workflow->getNextAuthenticationUrl();

        $this->assertEquals('/url-access-validation', $redirectUrl);
        $this->assertFalse($workflow->isFinished());

        // --- mimic what the coord plugin does at /url-access-validation
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $sfAction, $manager);
        $this->assertEquals('/url-access-validation', $this->workflowState->getCurrentActionUrl());
        $this->assertEquals($checkResult, $sfAction);

        // -- in the controller of the first step at /url-access-validation
        $workflow = $this->standardWorkflow->getWorkflow();
        $currentStep = $workflow->getCurrentStep();
        $this->assertNotNull($currentStep);
        $this->assertEquals('access_validation', $currentStep->getName());
        $this->assertTrue($workflow->isCurrentStep('access_validation'));

        // let's assume that the process of the action is ok, the action ends with:
        $redirectUrl = $workflow->getNextAuthenticationUrl();
        // the workflow should be finished
        $this->assertEquals('http://localhost/home', $redirectUrl);

        $this->assertNull($workflow->getCurrentStep());
        $this->assertTrue($workflow->isFinished());
        $this->assertTrue($workflow->isSuccess());

        // --- mimic what the coord plugin does at /home
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $homeAction, $manager);
        $this->assertNull($checkResult);

        // end
        $this->assertNull($this->standardWorkflow->getWorkflow());
        $this->assertTrue($isAuthenticated);
        $this->assertEquals($this->workflowState::END_STATUS_SUCCESS, $this->workflowState->getEndStatus());
    }

    public function testOneStepFailing()
    {
        $this->accountManager->setSearchResult(AccountWorkflowAdapterForTest::SEARCH_RESULT_ACCOUNT_NOT_SUPPORTED);

        $manager = new AuthenticatorManager([ new AuthManagerForTest([]) ]);

        // --- setup step actions
        $homeAction  = new \jSelectorDebugAction('classic', 'testapp', 'default:home');
        $valAction = new \jSelectorDebugAction('classic', 'testapp', 'validation:index');
        $this->evDispatcher->addWorkflowActionForStep(
            'access_validation',
            new Workflow\WorkflowAction('/url-access-validation', [
                $valAction
            ])
        );

        // --- start action: the action that authenticated the user initialize the workflow
        $workflow = $this->standardWorkflow->start($this->workflowState);
        $workflow->setFinalUrl('http://localhost/home');

        // --- then it redirects to the next url
        $redirectUrl = $workflow->getNextAuthenticationUrl();

        $this->assertEquals('/url-access-validation', $redirectUrl);
        $this->assertFalse($workflow->isFinished());

        // --- mimic what the coord plugin does at /url-access-validation
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $valAction, $manager);
        $this->assertEquals('/url-access-validation', $this->workflowState->getCurrentActionUrl());
        $this->assertEquals($checkResult, $valAction);

        // -- in the controller of the first step at /url-access-validation
        $workflow = $this->standardWorkflow->getWorkflow();
        $currentStep = $workflow->getCurrentStep();
        $this->assertNotNull($currentStep);
        $this->assertEquals('access_validation', $currentStep->getName());
        $this->assertTrue($workflow->isCurrentStep('access_validation'));

        // let's assume that the process of the action is failing.
        // it should cancel the whole authentication process.
        $workflow->cancel('The reason');

        $redirectUrl = $workflow->getNextAuthenticationUrl();
        // the workflow should be finished
        $this->assertEquals('http://localhost/home', $redirectUrl);

        $this->assertNull($workflow->getCurrentStep());
        $this->assertTrue($workflow->isFinished());
        $this->assertFalse($workflow->isSuccess());

        // --- mimic what the coord plugin does at /home
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $homeAction, $manager);
        $this->assertNull($checkResult);

        // end
        $this->assertNull($this->standardWorkflow->getWorkflow());
        $this->assertFalse($isAuthenticated);
        $this->assertEquals($this->workflowState::END_STATUS_FAIL, $this->workflowState->getEndStatus());
    }

    public function testStepsWithAccount()
    {
        $this->accountManager->setSearchResult(AccountWorkflowAdapterForTest::SEARCH_RESULT_ACCOUNT_FOUND);

        $manager = new AuthenticatorManager([ new AuthManagerForTest([]) ]);

        // --- setup step actions
        $homeAction  = new \jSelectorDebugAction('classic', 'testapp', 'default:home');
        $sfAction = new \jSelectorDebugAction('classic', 'testapp', 'secondfactor:index');
        $valAction = new \jSelectorDebugAction('classic', 'testapp', 'validation:index');
        $this->evDispatcher->setListenerForStep(
            'check_account',
            function(Workflow\Event\CheckAccountEvent $event){
                $event->getAccount()->data = 'check_account called';
            });

        $this->evDispatcher->addWorkflowActionForStep(
            'second_factor',
            new Workflow\WorkflowAction('/url-2fa', [
                $sfAction
            ])
        );
        $this->evDispatcher->addWorkflowActionForStep(
            'access_validation',
            new Workflow\WorkflowAction('/url-access-validation', [
                $valAction
            ])
        );

        // --- start action: the action that authenticated the user initialize the workflow
        $workflow = $this->standardWorkflow->start($this->workflowState);
        $workflow->setFinalUrl('http://localhost/home');

        $givenAccount = $this->workflowState->getTemporaryUser()->getAccount();
        $this->assertNotNull($givenAccount);
        $this->assertEquals('123', $givenAccount->getAccountId());
        $this->assertEquals('bob', $givenAccount->getUserName());
        $this->assertEquals('bob@example.com', $givenAccount->getEmail());
        $this->assertNull($givenAccount->data);

        // --- then it redirects to the next url which is the second factor url
        $redirectUrl = $workflow->getNextAuthenticationUrl();

        $this->assertEquals('/url-2fa', $redirectUrl);
        $this->assertFalse($workflow->isFinished());

        // -- we check that the check_account step was called
        $this->assertEquals('check_account called', $givenAccount->data);

        // --- mimic what the coord plugin does at /url-2fa
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $sfAction, $manager);
        $this->assertEquals('/url-2fa', $this->workflowState->getCurrentActionUrl());
        $this->assertEquals($checkResult, $sfAction);

        // -- in the controller of the first step at /url-2fa
        $workflow = $this->standardWorkflow->getWorkflow();
        $currentStep = $workflow->getCurrentStep();
        $this->assertNotNull($currentStep);
        $this->assertEquals('second_factor', $currentStep->getName());
        $this->assertTrue($workflow->isCurrentStep('second_factor'));

        // --- then it redirects to the next url
        $redirectUrl = $workflow->getNextAuthenticationUrl();

        $this->assertEquals('/url-access-validation', $redirectUrl);
        $this->assertFalse($workflow->isFinished());

        // --- mimic what the coord plugin does at /url-access-validation
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $valAction, $manager);
        $this->assertEquals('/url-access-validation', $this->workflowState->getCurrentActionUrl());
        $this->assertEquals($checkResult, $valAction);

        // -- in the controller of the second step at /url-access-validation
        $workflow = $this->standardWorkflow->getWorkflow();
        $currentStep = $workflow->getCurrentStep();
        $this->assertNotNull($currentStep);
        $this->assertEquals('access_validation', $currentStep->getName());
        $this->assertTrue($workflow->isCurrentStep('access_validation'));

        // let's assume that the process of the action is ok, the action ends with:
        $redirectUrl = $workflow->getNextAuthenticationUrl();
        // the workflow should be finished
        $this->assertEquals('http://localhost/home', $redirectUrl);

        $this->assertNull($workflow->getCurrentStep());
        $this->assertTrue($workflow->isFinished());
        $this->assertTrue($workflow->isSuccess());

        // --- mimic what the coord plugin does at /home
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $homeAction, $manager);
        $this->assertNull($checkResult);

        // end
        $this->assertNull($this->standardWorkflow->getWorkflow());
        $this->assertTrue($isAuthenticated);
        $this->assertEquals($this->workflowState::END_STATUS_SUCCESS, $this->workflowState->getEndStatus());
    }

    // FIMX test with account auto


    public function testStepsWithMissingAccount()
    {
        //$this->accountManager->setSearchResult(AccountWorkflowAdapterForTest::SEARCH_RESULT_ACCOUNT_FOUND);
        $manager = new AuthenticatorManager([ new AuthManagerForTest([]) ]);

        // --- setup step actions
        $homeAction  = new \jSelectorDebugAction('classic', 'testapp', 'default:home');
        $createAccountAction = new \jSelectorDebugAction('classic', 'testapp', 'account:create');
        $sfAction = new \jSelectorDebugAction('classic', 'testapp', 'secondfactor:index');
        $valAction = new \jSelectorDebugAction('classic', 'testapp', 'validation:index');

        $this->accountManager->setActionToCreateAccount(
            new Workflow\WorkflowAction('/url-create-account', [
                $createAccountAction
        ]));

        $this->evDispatcher->addWorkflowActionForStep(
            'second_factor',
            new Workflow\WorkflowAction('/url-2fa', [
                $sfAction
            ])
        );

        $this->evDispatcher->setListenerForStep(
            'check_account',
            function(Workflow\Event\CheckAccountEvent $event){
                $event->getAccount()->data = 'check_account called';
            });

        $this->evDispatcher->addWorkflowActionForStep(
            'access_validation',
            new Workflow\WorkflowAction('/url-access-validation', [
                $valAction
            ])
        );

        // --- start action: the action that authenticated the user initialize the workflow
        $workflow = $this->standardWorkflow->start($this->workflowState);
        $workflow->setFinalUrl('http://localhost/home');

        $givenAccount = $this->workflowState->getTemporaryUser()->getAccount();
        $this->assertNull($givenAccount);

        // --- then it redirects to the next url which is the create account url
        $redirectUrl = $workflow->getNextAuthenticationUrl();

        $this->assertEquals('/url-create-account', $redirectUrl);
        $this->assertFalse($workflow->isFinished());

        // --- mimic what the coord plugin does at /url-create-account
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction(
            $isAuthenticated, $createAccountAction, $manager);
        $this->assertEquals('/url-create-account', $this->workflowState->getCurrentActionUrl());
        $this->assertEquals($checkResult, $createAccountAction);

        // --- in the controller of the first step at /url-create-account : creation of the account
        $workflow = $this->standardWorkflow->getWorkflow();
        $currentStep = $workflow->getCurrentStep();
        $this->assertNotNull($currentStep);
        $this->assertEquals('create_account', $currentStep->getName());
        $this->assertTrue($workflow->isCurrentStep('create_account'));
        // account created, give information to the workflow
        $account = new AccountForTest('123', 'bob', 'Bob Morane', 'bob@example.com');
        $workflow->setNewAccountToUser($account);

        // --- then it redirects to the next url : check_account should be, and second_factor
        // ignored, as there is no second factor step for newly created account
        $redirectUrl = $workflow->getNextAuthenticationUrl();
        $this->assertEquals('check_account called', $account->data);

        $this->assertEquals('/url-access-validation', $redirectUrl);
        $this->assertFalse($workflow->isFinished());

        // --- mimic what the coord plugin does at /url-access-validation
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $valAction, $manager);
        $this->assertEquals('/url-access-validation', $this->workflowState->getCurrentActionUrl());
        $this->assertEquals($checkResult, $valAction);

        // -- in the controller of the second step at /url-access-validation
        $workflow = $this->standardWorkflow->getWorkflow();
        $currentStep = $workflow->getCurrentStep();
        $this->assertNotNull($currentStep);
        $this->assertEquals('access_validation', $currentStep->getName());
        $this->assertTrue($workflow->isCurrentStep('access_validation'));

        // let's assume that the process of the action is ok, the action ends with:
        $redirectUrl = $workflow->getNextAuthenticationUrl();
        // the workflow should be finished
        $this->assertEquals('http://localhost/home', $redirectUrl);

        $this->assertNull($workflow->getCurrentStep());
        $this->assertTrue($workflow->isFinished());
        $this->assertTrue($workflow->isSuccess());

        // --- mimic what the coord plugin does at /home
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $homeAction, $manager);
        $this->assertNull($checkResult);

        // end
        $this->assertNull($this->standardWorkflow->getWorkflow());
        $this->assertTrue($isAuthenticated);
        $this->assertEquals($this->workflowState::END_STATUS_SUCCESS, $this->workflowState->getEndStatus());
    }

    public function testCheckAccountSetAccountPropagates()
    {
        $this->accountManager->setActionToCreateAccount(null);
        $this->accountManager->setAccountToFind(new AccountForTest('123', 'bob', 'Bobold Morane', 'bob-old@example.com'));

        $sfAction = new \jSelectorDebugAction('classic', 'testapp', 'secondfactor:index');
        $valAction = new \jSelectorDebugAction('classic', 'testapp', 'validation:index');

        $this->evDispatcher->setListenerForStep(
            'check_account',
            function(Workflow\Event\CheckAccountEvent $event){
                $updatedAccount = new AccountForTest(
                    '123', 'bob', 'Robert Morane', 'bob.new@example.com'
                );
                $event->setAccount($updatedAccount);
            });

        $this->evDispatcher->addWorkflowActionForStep(
            'second_factor',
            new Workflow\WorkflowAction('/url-2fa', [
                $sfAction
            ])
        );
        $this->evDispatcher->addWorkflowActionForStep(
            'access_validation',
            new Workflow\WorkflowAction('/url-access-validation', [
                $valAction
            ])
        );

        $workflow = $this->standardWorkflow->start($this->workflowState);
        $workflow->setFinalUrl('http://localhost/home');

        $redirectUrl = $workflow->getNextAuthenticationUrl();
        $this->assertEquals('/url-2fa', $redirectUrl);

        $account = $this->workflowState->getTemporaryUser()->getAccount();
        $this->assertNotNull($account);
        $this->assertEquals('Robert Morane', $account->getRealName());
        $this->assertEquals('bob.new@example.com', $account->getEmail());
    }

}
