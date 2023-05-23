<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Event;


use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\Workflow\WorkflowAction;

class WorkflowStepEvent extends \jEvent
{
    /**
     * @var WorkflowAction[]
     */
    protected $actions = array();

    public function __construct($stepName, $transition, AuthUser $authenticatedUser, $idpId)
    {
        parent::__construct('AuthWorkflowStep',
            array(
                'stepName' => $stepName,
                'transition' => $transition,
                'user' => $authenticatedUser,
                'idpId' => $idpId
            ));
    }

    /**
     * @return string
     */
    public function getStepName()
    {
        return $this->_params['stepName'];
    }

    /**
     * @return string
     */
    public function getAppliedTransition()
    {
        return $this->_params['transition'];
    }

    /**
     * @return AuthUser
     */
    public function getUserBeingAuthenticated()
    {
        return $this->_params['user'];
    }

    /**
     * @return string
     */
    public function getIdpId()
    {
        return $this->_params['idpId'];
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
