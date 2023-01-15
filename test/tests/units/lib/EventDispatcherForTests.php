<?php

use Jelix\Authentication\Core\Workflow\Event\WorkflowStepEvent;

class EventDispatcherForTests  implements \Psr\EventDispatcher\EventDispatcherInterface
{

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
        switch($event->getStepName()) {
            case 'hasoneaction':
                $event->addAction(new \Jelix\Authentication\Core\Workflow\WorkflowAction('url1', []));
                break;
        }
        return $event;
    }
}