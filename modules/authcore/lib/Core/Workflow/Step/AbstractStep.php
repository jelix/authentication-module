<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow;


use Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * 
 */
abstract class AbstractStep implements StepInterface
{
    /**
     * @var string
     */
    protected $name = '';

    protected $transition = '';

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var WorkflowState
     */
    protected $workflowState;

    public function __construct($eventDispatcher, WorkflowState $workflowState)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->workflowState = $workflowState;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * start the step.
     *
     * @param string $transition the name of the transition that is applied to start the step
     * @return void
     */
    public function startStep($transition, WorkflowState $workflowState)
    {
        $event = new WorkflowStepEvent($this->name, $transition);
        $this->eventDispatcher->dispatch($event);
        $this->workflowState->setActions($event->getActions());
    }

    /**
     * Give the URL of a controller that need a user action for the step
     * 
     * It may return an empty string, if the step does not require a user action,
     * for example because the step is not relevant (for example for a 2FA plugin, 
     * 2FA is deactivated for the current user).
     * 
     * @return string|null
     */
    public function getNextActionUrl()
    {
        return $this->workflowState->getNextActionUrl();
    }

    /**
     *
     * @return string|null
     */
    public function getCurrentActionUrl()
    {
        return $this->workflowState->getCurrentActionUrl();
    }

    /**
     * Give the transition to apply when the step is finished, i.e. when
     * getNextActionUrl() returns null
     *
     * @return string the transition name
     */
    public function getTransition()
    {
        return $this->transition;
    }

    /**
     * It should check that the given action is an action allowed by the step.
     * If it is not the case, it should return null
     *
     * @param \jSelectorActFast $currentRequestAction the action asked to be executed
     * @return \jSelectorActFast|null the action to execute
     */
    public function getExpectedAction(\jSelectorActFast $currentRequestAction)
    {
       return $this->workflowState->getExpectedAction($currentRequestAction);
    }

    /**
     * @return WorkflowAction|null
     */
    public function getCurrentAction()
    {
        return $this->workflowState->getCurrentAction();
    }
}
