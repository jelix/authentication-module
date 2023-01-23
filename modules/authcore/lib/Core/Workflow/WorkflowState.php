<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow;

use Jelix\Authentication\Core\AuthSession\AuthUser;

/**
 * Embedded data to be share with steps objects and the workflow object
 */
class WorkflowState
{
    /**
     * @var AuthUser
     */
    protected $user;

    /**
     * The id of the identity provider
     */
    protected $idpId;

    /**
     * @var string
     */
    protected $finalUrl;


    protected $currentStepName = '';

    /**
     *
     * @var WorkflowAction[]  Actions for the current step
     */
    protected $actions = array();

    /**
     * @var WorkflowAction current action for the current step
     */
    protected $currentAction;

    const END_STATUS_NONE = 0;

    const END_STATUS_SUCCESS = 1;

    const END_STATUS_FAIL = -1;

    /**
     * @var int one of END_STATUS_* const.
     */
    protected $endStatus = 0;

    public function __construct(AuthUser $temporaryUser, $idpId)
    {
        $this->user = $temporaryUser;
        $this->idpId = $idpId;
    }

    /**
     * @return AuthUser
     */
    public function getTemporaryUser()
    {
        return $this->user;
    }

    public function getIdpId()
    {
        return $this->idpId;
    }

    public function setFinalUrl(string $url)
    {
        $this->finalUrl = $url;
    }

    public function getFinalUrl()
    {
        return $this->finalUrl;
    }

    public function setCurrentStepName(string $currentStepName)
    {
        $this->currentStepName = $currentStepName;
    }

    public function getCurrentStepName()
    {
        return $this->currentStepName;
    }

    public function setActions(array $actions)
    {
        $this->actions = $actions;
        $this->currentAction = null;
    }

    public function setNextAction(WorkflowAction $action)
    {
        array_unshift($this->actions, $action);
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
        if (count($this->actions)) {
            $this->currentAction = array_shift($this->actions);
            return $this->currentAction->getUrl();
        }
        $this->currentAction = null;
        return null;
    }

    /**
     * @return string|null
     */
    public function getCurrentActionUrl()
    {
        if ($this->currentAction) {
            return $this->currentAction->getUrl();
        }
        return null;
    }

    /**
     * It should check that the given action is an action allowed by the step.
     * If it is not the case, it should return null
     *
     * @param \jIActionSelector $currentRequestAction the action asked to be executed
     * @return \jIActionSelector|null the action to execute
     */
    public function getExpectedAction(\jIActionSelector $currentRequestAction)
    {
        if ($this->currentAction) {
            if (!$this->currentAction->isValidAction($currentRequestAction)) {
                return null;
            }
        }

        return $currentRequestAction;
    }


    /**
     * @return WorkflowAction|null
     */
    public function getCurrentAction()
    {
        return $this->currentAction;
    }

    /**
     * Should be called by the latest step of the workflow,
     * to indicate a success or a fail
     * @param int $endStatus one of END_STATUS_* const
     * @return void
     */
    public function setEndStatus($endStatus)
    {
        $this->endStatus = $endStatus;
    }

    public function getEndStatus()
    {
        return $this->endStatus;
    }

}