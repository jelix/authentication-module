<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022 Laurent Jouanneau
 * @link     http://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core;

use Jelix\Authentication\Core\AuthSession\AuthUser;

/**
 * Manage the authentication workflow
 */
class Workflow
{
    const ACTION_DOING = 1;
    const ACTION_NEXT = 2;
    const ACTION_FINISH = 3;
    const ACTION_CANCEL = 4;

    /**
     * @var integer indicate what to do after the current step
     */
    protected $nextAction = 2;

    /**
     * @var AuthUser
     */
    protected $user;

    /**
     * @var string
     */
    protected $finalUrl;

    /**
     * @var string
     */
    protected $cancelUrl;

    /**
     * @var WorkflowStep[] the list of steps
     */
    protected $stepList = [];

    /**
     * index of the step that is currently executed
     */
    protected $currentStepIndex = 0;

    /**
     * The id of the identity provider
     */
    protected $idpId;

    /**
     * 
     *
     * @param AuthUser $user the authenticated user
     * @param string $idpId The id of the identity provider that has authenticated the user
     * @param WorkflowStep[] $stepList
     * @return void
     */
    public function __construct(AuthUser $temporaryUser, $idpId, array $stepList)
    {
        $this->user = $temporaryUser;
        $this->nextAction = self::ACTION_NEXT;
        $this->stepList = $stepList;
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

    /**
     * Set the status workflow as canceled. 
     * 
     * No more steps will be executed, and the workflow will end at the cancel url.
     * There will not be authenticated user at the current session
     */
    public function cancel()
    {
        $this->nextAction = self::ACTION_CANCEL;
    }

    /**
     * Set the status workflow as finished. 
     * 
     * No more steps will be executed, and the workflow will end at the finish url.
     * The user will be authenticated.
     */
    public function finish()
    {
        $this->nextAction = self::ACTION_FINISH;
    }

    public function willContinue()
    {
        $this->nextAction = self::ACTION_NEXT;
    }

    public function isFinished()
    {
        return $this->nextAction == self::ACTION_FINISH;
    }

    public function isDoing()
    {
        return $this->nextAction == self::ACTION_DOING;
    }

    public function isCanceled()
    {
        return $this->nextAction == self::ACTION_CANCEL;
    }

    public function setFinalUrl(string $url)
    {
        $this->finalUrl = $url;
    }

    public function setCancelUrl(string $url)
    {
        $this->cancelUrl = $url;
    }

    /**
     * Get url of the next step, and the next step will be the current step
     * 
     * If the workflow is canceled or finished, the url of the corresponding
     * status is given.
     * 
     * @return string the url to redirect to
     */
    public function getAuthenticationNextStepUrl()
    {
        if ($this->nextAction == self::ACTION_CANCEL) {
            return $this->cancelUrl;
        } else if ($this->nextAction == self::ACTION_FINISH) {
            return $this->finalUrl;
        }

        // For ACTION_NEXT AND ACTION_DOING next action
        $this->nextAction = self::ACTION_DOING;
        while ($step = $this->getNextAuthenticationStep()) {

            $url = $step->getUrl();
            // No url means that the step does not require a user action
            if ($url) {
                return $url;
            }
        }
        $this->nextAction = self::ACTION_FINISH;

        return $this->finalUrl;
    }

    /**
     * @return WorkflowStep|null
     */
    protected function getNextAuthenticationStep()
    {
        if ($this->currentStepIndex + 1 < count($this->stepList)) {
            return $this->stepList[++$this->currentStepIndex];
        } else {
            return null;
        }
    }

    /**
     * @return WorkflowStep|null
     */
    public function getCurrentStep()
    {
        if ($this->currentStepIndex < count($this->stepList)) {
            return $this->stepList[$this->currentStepIndex];
        } else {
            return null;
        }
    }
}
