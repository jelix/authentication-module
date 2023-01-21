<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow;

/**
 * Interface for objects implementing a step of an authentication
 */
interface StepInterface
{
    /**
     * Get the name of the step
     *
     * It is used into the authentication workflow, as the start or the end of a
     * transition.
     *
     * @return string the name of the step
     */
    public function getName();

    /**
     * Starts the execution of the step
     *
     * It is launched when a transition ends with this step.
     * It is the opportunity for the step to launch events, to decide of the next
     * transition (with will be returned by `getTransition()`)
     * or to decide of the next action url.
     *
     * @param string $transition the transition name
     * @param WorkflowState $workflowState
     * @return mixed
     */
    public function startStep($transition, WorkflowState $workflowState);

    /**
     * Give the url where to go to execute the step, if needed
     *
     * If the steps is one or more web pages (and so, urls), this method
     * should give the url of the next page.
     *
     * @return string
     */
    public function getNextActionUrl();

    /**
     * Give the url of the current web page to execute the step,
     * when the steps has pages.
     *
     * @return string|null the url or null if there is no page to display
     *                     for the step.
     */
    public function getCurrentActionUrl();

    /**
     * @return mixed
     */
    public function getTransition();

    /**
     * @param \jSelectorActFast $currentRequestAction
     * @return mixed
     */
    public function getExpectedAction(\jSelectorActFast $currentRequestAction);

}