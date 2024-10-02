<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Step;

use Jelix\Authentication\Core\Workflow\WorkflowState;
use Psr\EventDispatcher\EventDispatcherInterface;

class GenericStep extends AbstractStep
{

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param string $name
     */
    public function __construct($eventDispatcher, WorkflowState $workflowProperties, $name, $transition='')
    {
        $this->transition = $transition;
        $this->name = $name;
        parent::__construct($eventDispatcher, $workflowProperties);
    }

}