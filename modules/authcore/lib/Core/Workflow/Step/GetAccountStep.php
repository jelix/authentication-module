<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Step;


use Jelix\Authentication\Core\Workflow\Event\GetAccountEvent;
use Jelix\Authentication\Core\Workflow\WorkflowState;

class GetAccountStep extends AbstractStep
{

    protected $name = 'get_account';

    protected $transition = '';

    /**
     * @return void
     */
    public function startStep($transition, WorkflowState $workflowState)
    {
        $event = new GetAccountEvent($workflowState->getTemporaryUser());

        $this->eventDispatcher->dispatch($event);

        if ($event->hasAccountResponse()) {
            $id = $event->getAccountId();
            if ($id === '') {
                $this->transition = 'account_not_found';
            }
            else {
                $this->transition = 'account_found';
                $workflowState->getTemporaryUser()->setAccount(
                    $event->getAccount()
                );
            }
        }
        else {
            $this->transition = 'account_not_supported';
        }
    }


}