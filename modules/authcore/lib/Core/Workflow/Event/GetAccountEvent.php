<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     http://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Event;


use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\AuthSession\UserAccountInterface;

class GetAccountEvent extends WorkflowStepEvent
{
    /**
     * @var UserAccountInterface
     */
    protected $account = null;

    public function __construct(AuthUser $authenticatedUser, $idpId)
    {
        parent::__construct('get_account', 'start', $authenticatedUser, $idpId);
    }

    /**
     * Should be called by listeners which found an account corresponding to the authenticated user
     *
     * @param string $accountId
     * @param UserAccountInterface $account
     * @return void
     */
    public function setAccount(UserAccountInterface $account)
    {
        $this->add(['id' => $account->getAccountId()]);
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

    /**
     * @return bool
     */
    public function hasAccountResponse()
    {
       return ($this->getResponseByKey('id') !== null);
    }

    /**
     * @return string|null
     */
    public function getAccountId()
    {
        $idList = $this->getResponseByKey('id');
        if ($idList) {
            return $idList[0];
        }
        return null;
    }

    /**
     * @return UserAccountInterface|null
     */
    public function getAccount()
    {
        return $this->account;
    }

}
