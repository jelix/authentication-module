<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow;


/**
 * Represents a page to where the user will be redirected during an authentication workflow
 */
class WorkflowAction
{
    /**
     * @var string
     */
    protected $startUrl;

    /**
     * @var \jSelectorAct[]
     */
    protected $possibleRequestActions = array();

    /**
     * priority of the action
     */
    protected $priority = 99;

    /**
     * @param string $startUrl the URL of a controller to which the user will be redirected
     * @param \jSelectorAct[] $possibleRequestActions list of possible actions that are executed after the startUrl
     * @param int $priority  the priority of the action, compare to other WorkflowAction objects of a step
     */
    public function __construct($startUrl, $possibleRequestActions, $priority = 99)
    {
        $this->possibleRequestActions = $possibleRequestActions;
        $this->startUrl = $startUrl;
        $this->priority = $priority;
    }

    /**
     * Give the URL of a controller to which the user will be redirected
     * 
     * Most of the time the action show a form
     * 
     * @return string
     */
    public function getUrl()
    {
        return $this->startUrl;
    }

    /**
     * A logic action may require to go throw several url (like a form, then the submit url)
     * 
     * This method checks that the given request action belongs to the logic action. 
     * 
     * @return bool
     */
    public function isValidAction(\jSelectorActFast $possibleRequestAction)
    {
        foreach($this->possibleRequestActions as $pra) {
            if ($pra->isEqualTo($possibleRequestAction)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return \jSelectorAct|null
     */
    /*public function getAction()
    {
        return null;
    }*/

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
