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
 * Manage the authentication workflow
 */
class Workflow
{
    /**
     * @var StepInterface[] the list of steps
     */
    protected $stepList = [];

    protected $transitions = [];

    /**
     * @var WorkflowState
     */
    protected $state;

    /**
     * initialize the workflow object
     *
     * @param WorkflowState $properties
     * @return void
     */
    public function __construct(WorkflowState $state)
    {
        $this->state = $state;
    }

    /**
     * Initialize the workflow
     *
     * @param StepInterface[] $stepList
     * @param string[][] $transitions
     * @param string $firstStepName
     * @return void
     */
    public function setup(array $stepList, array $transitions, $firstStepName)
    {
        foreach($stepList as $step) {
            $this->stepList[$step->getName()] = $step;
        }
        $this->transitions = $transitions;
        $this->transitions['start'] = array(
            'from' => '',
            'to' => $firstStepName
        );

        if (!isset($this->stepList[$firstStepName])) {
            throw new \InvalidArgumentException('Unknown first step name');
        }
    }

    /**
     * @return StepInterface|null
     */
    public function getCurrentStep()
    {
        $current = $this->state->getCurrentStepName();
        if ($current) {
            return $this->stepList[$current];
        }
        return null;
    }

    public function isCurrentStep($stepName)
    {
        $current = $this->state->getCurrentStepName();
        if (!$current || $current != 'mystepname') {
            return false;
        }
        return true;
    }

    public function getCurrentStepUrl()
    {
        $url = '';
        $step = $this->getCurrentStep();
        if($step) {
            $url = $step->getCurrentActionUrl();
        }
        return $url;
    }


    public function canApply(string $transitionName)
    {
        if (!isset($this->transitions[$transitionName])) {
            throw new \Exception('Unknown transition "'.$transitionName.'"');
        }
        $trans = $this->transitions[$transitionName];
        $currentStep = $this->state->getCurrentStepName();
        if (is_array($trans['from'])) {
            return in_array($currentStep, $trans['from']);
        }
        if ($trans['from'] == '*') {
            return true;
        }
        return $currentStep == $trans['from'];
    }

    public function apply(string $transitionName)
    {
        if ($this->canApply($transitionName)) {
            $this->state->setCurrentStepName($this->transitions[$transitionName]['to']);
            $step = $this->getCurrentStep();
            if ($step) {
                $step->startStep($transitionName, $this->state);
            }
            return true;
        }
        return false;
    }

    /**
     * @return AuthUser
     */
    public function getTemporaryUser()
    {
        return $this->state->getTemporaryUser();
    }

    public function getIdpId()
    {
        return $this->state->getIdpId();
    }

    /**
     * Set the status workflow as canceled. 
     * 
     * No more steps will be executed, and the workflow will end.
     * There will not be authenticated user at the current session
     */
    public function cancel()
    {
        if (!$this->apply('fail')) {
            // no fail transition, or current step do not apply fail, we
            // force to terminate.
            $this->state->setCurrentStepName('');
            $this->state->setEndStatus($this->state::END_STATUS_FAIL);
        }
    }

    public function isFinished()
    {
        return ($this->state->getEndStatus() !== $this->state::END_STATUS_NONE)
            || $this->state->getCurrentStepName() == '';
    }

    public function isSuccess()
    {
        return ($this->state->getEndStatus() === $this->state::END_STATUS_SUCCESS);
    }

    public function setFinalUrl(string $url)
    {
        $this->state->setFinalUrl($url);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getNextAuthenticationUrl()
    {
        $step = $this->getCurrentStep();
        while ($step) {
            $url = $step->getNextActionUrl();
            // there a URL where to redirect the user, to continue the
            // step
            if ($url) {
                return $url;
            }

            // No url means that the step does not require (anymore) a user action

            // Retrieve the transition that the step indicate
            $transition = $step->getTransition();

            if ($transition == '') {
                // no more possible transition, so no more step
                $this->state->setCurrentStepName('');
                break;
            }
            if (!$this->canApply($transition)) {

                throw new \Exception('bad transition '.$transition);
            }
            $this->apply($transition);
            $step = $this->getCurrentStep();
        }
        return $this->state->getFinalUrl();
    }
}
