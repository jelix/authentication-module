<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022 Laurent Jouanneau
 * @link     http://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core;


/**
 * 
 */
class WorkflowStep
{
    protected $url;

    public function __construct()
    {
    }

    /**
     * Give the URL of a controller that need a user action for the step
     * 
     * It may returns an empty string, if the step does not require a user action,
     * for example because the step is not relevant (for example for a 2FA plugin, 
     * 2FA is deactivated for the current user).
     * 
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return \jSelectorAct|null
     */
    public function getAction()
    {
        return null;
    }
}
