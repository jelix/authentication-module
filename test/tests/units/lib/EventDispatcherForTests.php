<?php

use Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent;
use Jelix\Authentication\Core\Workflow\WorkflowAction;

class EventDispatcherForTests  implements \Psr\EventDispatcher\EventDispatcherInterface
{
    /**
     * @var callback[]
     */
    protected $listeners = array();

    /**
     * @var WorkflowAction[][]
     */
    protected $wkflActions = array();

    public function __construct()
    {
    }

    /**
     * Dispatch the given event object to all listeners which listen to this event
     *
     * @param WorkflowStepEvent $event the event object. It may implement StoppableEventInterface and EventInterface
     * @return WorkflowStepEvent the given event
     */
    public function dispatch(object $event)
    {
        if ($event instanceof WorkflowStepEvent) {
            $stepName = $event->getStepName();
            if (isset($this->listeners[$stepName])) {
                $callback = $this->listeners[$stepName];
                $callback($event);
            }
            if (isset($this->wkflActions[$stepName])) {
                foreach ($this->wkflActions[$stepName] as $action) {
                    $event->addAction($action);
                }
            }
        }

        return $event;
    }

    public function setListenerForStep($stepName, $callback)
    {
        $this->listeners[$stepName] = $callback;
    }

    /**
     * @param string $stepName
     * @param WorkflowAction|WorkflowAction[] $action
     * @return void
     */
    public function addWorkflowActionForStep($stepName, $action)
    {
        if (!isset($this->wkflActions[$stepName])) {
            $this->wkflActions[$stepName] = array();
        }

        if (is_array($action)) {
            $this->wkflActions[$stepName] = array_merge($this->wkflActions[$stepName] , $action);
        }
        else {
            $this->wkflActions[$stepName][] = $action;
        }
    }
}