<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     http://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Event;


class GetAccountEvent extends WorkflowStepEvent
{

    protected $account = null;

    public function __construct()
    {
        parent::__construct('get_account', 'start');
    }

    /**
     * Should be called by listeners which found an account corresponding to the authenticated user
     *
     * @param string $accountId
     * @param object $account
     * @return void
     */
    public function setAccount(string $accountId, object $account)
    {
        $this->add(['id' => $accountId]);
        $this->account = $account;
    }

    /**
     * Should be called by listeners if they don't find the account corresponding to the authenticated user
     *
     * It shouldn't be called by listeners that don't search accounts.
     * @return void
     */
    public function setUnknownAccount()
    {
        $this->add(['id' => '']);
        $this->account = null;
    }

    public function hasAccountResponse()
    {
       return ($this->getResponseByKey('id') !== null);
    }

    public function getAccountId()
    {
        $idList = $this->getResponseByKey('id');
        if ($idList) {
            return $idList[0];
        }
        return null;
    }

    public function getAccount()
    {
        return $this->account;
    }

}
