<?php

use PHPUnit\Framework\TestCase;
use Jelix\Authentication\Core\Workflow;
use Jelix\Authentication\Core\AuthSession\AuthUser;

class workflowTest extends TestCase
{
    function testSimpleTransition()
    {
        $user = new AuthUser('laurent', array());
        $wkfState = new Workflow\WorkflowState($user, 'loginpass');
        $workflow = new Workflow\Workflow($wkfState);
        $evDispatcher = new EventDispatcherForTests();

        $steps = array (
           new Workflow\Step\GenericStep($evDispatcher, $wkfState, 'first'),
           new Workflow\Step\GenericStep($evDispatcher, $wkfState, 'second'),
        );

        $transitions = array(
            'transit1' => array(
                'from' => 'first',
                'to' => 'second'
            )
        );

        $workflow->setup($steps, $transitions, 'first');
        $workflow->apply('start');

        $this->assertEquals('first', $workflow->getCurrentStep()->getName());

        $this->assertTrue($workflow->canApply('transit1'));
        $workflow->apply('transit1');

        $this->assertEquals('second', $workflow->getCurrentStep()->getName());
    }

    function testStepGivingTransaction()
    {
        $user = new AuthUser('laurent', array());
        $wkfState = new Workflow\WorkflowState($user, 'loginpass');
        $workflow = new Workflow\Workflow($wkfState);
        $evDispatcher = new EventDispatcherForTests();

        $steps = array (
           new Workflow\Step\GenericStep($evDispatcher, $wkfState, 'first', 'transit1'),
           new Workflow\Step\GenericStep($evDispatcher, $wkfState, 'second'),
        );

        $transitions = array(
            'transit1' => array(
                'from' => 'first',
                'to' => 'second'
            )
        );

        $workflow->setup($steps, $transitions, 'first');
        $workflow->setFinalUrl('http://localhost');
        $workflow->apply('start');

        $this->assertEquals('first', $workflow->getCurrentStep()->getName());
        $url = $workflow->getNextAuthenticationUrl();
        $this->assertEquals('http://localhost', $url);
        $step = $workflow->getCurrentStep();
        $this->assertNotNull($step);
        $this->assertEquals('second', $step->getName());
    }

    function testStepOneAction()
    {
        $user = new AuthUser('laurent', array());
        $wkfState = new Workflow\WorkflowState($user, 'loginpass');
        $workflow = new Workflow\Workflow($wkfState);
        $evDispatcher = new EventDispatcherForTests();

        $steps = array (
           new Workflow\Step\GenericStep($evDispatcher, $wkfState, 'hasoneaction', 'transit1'),
           new Workflow\Step\GenericStep($evDispatcher, $wkfState, 'second'),
        );

        $transitions = array(
            'transit1' => array(
                'from' => 'hasoneaction',
                'to' => 'second'
            )
        );

        $workflow->setup($steps, $transitions, 'hasoneaction');
        $workflow->setFinalUrl('http://localhost');
        $workflow->apply('start');

        $this->assertEquals('hasoneaction', $workflow->getCurrentStep()->getName());
        // the url should be the one given by the EventDispatcherForTests
        $url = $workflow->getNextAuthenticationUrl();
        $this->assertEquals('url1', $url);
        $url = $workflow->getNextAuthenticationUrl();
        $this->assertEquals('http://localhost', $url);
        $step = $workflow->getCurrentStep();
        $this->assertNotNull($step);
        $this->assertEquals('second', $step->getName());
    }

}