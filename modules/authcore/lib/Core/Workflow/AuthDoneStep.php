<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow;


class AuthDoneStep extends AbstractStep
{
    protected $name = 'auth_done';


    /**
     * @return void
     */
    public function startStep($transition, WorkflowState $workflowState)
    {

    }


    public function getNextActionUrl()
    {
        return '';
    }
}