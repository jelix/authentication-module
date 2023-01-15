<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Event;


use Jelix\Authentication\Core\Workflow\WorkflowAction;

class WorkflowStepEvent extends \jEvent
{
    /**
     * @var WorkflowAction[]
     */
    protected $actions = array();

    public function __construct($stepName, $transition)
    {
        parent::__construct('AuthWorkflowStep',
            array(
                'stepName' => $stepName,
                'transition' => $transition
            ));
    }

    public function getStepName()
    {
        return $this->_params['stepName'];
    }

    public function getAppliedTransition()
    {
        return $this->_params['transition'];
    }

    /**
     * @return WorkflowAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    public function addAction(WorkflowAction $action)
    {
        $this->actions[] = $action;
    }
}
