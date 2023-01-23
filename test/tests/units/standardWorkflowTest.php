<?php

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

    public function setUp(): void
    {
        $varSessionHandler = new varAuthSessionHandler();
        $this->evDispatcher = new EventDispatcherForTests();
        $session = new AuthSession($varSessionHandler, $this->evDispatcher);
        $this->standardWorkflow = new Workflow\StandardWorkflow($session, $this->evDispatcher);
        $user = new AuthUser('laurent', array());
        $this->workflowState = new Workflow\WorkflowState($user, 'baridp');

    }

    public function testEmptyWorkflowSuccess()
    {
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
        $manager = new AuthenticatorManager([ new AuthManagerForTest([]) ]);

        // --- setup step actions
        $homeAction  = new \jSelectorDebugAction('classic', 'testapp', 'default:home');
        $sfAction = new \jSelectorDebugAction('classic', 'testapp', 'secondfactor:index');
        $this->evDispatcher->addWorkflowActionForStep(
            'access_validation',
            new Workflow\WorkflowAction('/url1', [
                $sfAction
            ])
        );

        // --- start action: the action that authenticated the user initialize the workflow
        $workflow = $this->standardWorkflow->start($this->workflowState);
        $workflow->setFinalUrl('http://localhost/home');

        // --- then it redirects to the next url
        $redirectUrl = $workflow->getNextAuthenticationUrl();

        $this->assertEquals('/url1', $redirectUrl);
        $this->assertFalse($workflow->isFinished());

        // --- mimic what the coord plugin does at /url1
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $sfAction, $manager);
        $this->assertEquals('/url1', $this->workflowState->getCurrentActionUrl());
        $this->assertEquals($checkResult, $sfAction);

        // -- in the controller of the first step at /url1
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

        $manager = new AuthenticatorManager([ new AuthManagerForTest([]) ]);

        // --- setup step actions
        $homeAction  = new \jSelectorDebugAction('classic', 'testapp', 'default:home');
        $valAction = new \jSelectorDebugAction('classic', 'testapp', 'validation:index');
        $this->evDispatcher->addWorkflowActionForStep(
            'access_validation',
            new Workflow\WorkflowAction('/url1', [
                $valAction
            ])
        );

        // --- start action: the action that authenticated the user initialize the workflow
        $workflow = $this->standardWorkflow->start($this->workflowState);
        $workflow->setFinalUrl('http://localhost/home');

        // --- then it redirects to the next url
        $redirectUrl = $workflow->getNextAuthenticationUrl();

        $this->assertEquals('/url1', $redirectUrl);
        $this->assertFalse($workflow->isFinished());

        // --- mimic what the coord plugin does at /url1
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $valAction, $manager);
        $this->assertEquals('/url1', $this->workflowState->getCurrentActionUrl());
        $this->assertEquals($checkResult, $valAction);

        // -- in the controller of the first step at /url1
        $workflow = $this->standardWorkflow->getWorkflow();
        $currentStep = $workflow->getCurrentStep();
        $this->assertNotNull($currentStep);
        $this->assertEquals('access_validation', $currentStep->getName());
        $this->assertTrue($workflow->isCurrentStep('access_validation'));

        // let's assume that the process of the action is failing.
        // it should cancel the whole authentication process.
        $workflow->cancel();

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
        $manager = new AuthenticatorManager([ new AuthManagerForTest([]) ]);

        // --- setup step actions
        $homeAction  = new \jSelectorDebugAction('classic', 'testapp', 'default:home');
        $sfAction = new \jSelectorDebugAction('classic', 'testapp', 'secondfactor:index');
        $valAction = new \jSelectorDebugAction('classic', 'testapp', 'validation:index');
        $this->evDispatcher->setListenerForStep(
            'get_account',
            function(Workflow\Event\GetAccountEvent $event){
                $account = new AccountForTest('123', 'bob', 'bob@example.com');
                $event->setAccount($account);
        });
        $this->evDispatcher->addWorkflowActionForStep(
            'second_factor',
            new Workflow\WorkflowAction('/url1', [
                $sfAction
            ])
        );
        $this->evDispatcher->addWorkflowActionForStep(
            'access_validation',
            new Workflow\WorkflowAction('/url2', [
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

        // --- then it redirects to the next url
        $redirectUrl = $workflow->getNextAuthenticationUrl();

        $this->assertEquals('/url1', $redirectUrl);
        $this->assertFalse($workflow->isFinished());

        // --- mimic what the coord plugin does at /url1
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $sfAction, $manager);
        $this->assertEquals('/url1', $this->workflowState->getCurrentActionUrl());
        $this->assertEquals($checkResult, $sfAction);

        // -- in the controller of the first step at /url1
        $workflow = $this->standardWorkflow->getWorkflow();
        $currentStep = $workflow->getCurrentStep();
        $this->assertNotNull($currentStep);
        $this->assertEquals('second_factor', $currentStep->getName());
        $this->assertTrue($workflow->isCurrentStep('second_factor'));

        // --- then it redirects to the next url
        $redirectUrl = $workflow->getNextAuthenticationUrl();

        $this->assertEquals('/url2', $redirectUrl);
        $this->assertFalse($workflow->isFinished());

        // --- mimic what the coord plugin does at /url2
        $isAuthenticated = false;
        $checkResult = $this->standardWorkflow->checkWorkflowAndAction($isAuthenticated, $valAction, $manager);
        $this->assertEquals('/url2', $this->workflowState->getCurrentActionUrl());
        $this->assertEquals($checkResult, $valAction);

        // -- in the controller of the second step at /url2
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

}