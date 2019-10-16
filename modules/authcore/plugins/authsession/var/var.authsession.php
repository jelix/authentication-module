<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

use Jelix\Authentication\Core\AuthSession\AuthSessionHandlerInterface;
use Jelix\Authentication\Core\AuthSession\AuthUser;


class varAuthSessionHandler implements AuthSessionHandlerInterface {

    /**
     * @var AuthUser|null
     */
    protected $user = null;

    /**
     * @var string|null
     */
    protected $identProviderId = null;

    /**
     * @param AuthUser $user
     * @param string $IPid
     */
    public function setSessionUser(AuthUser $user, $IdpId)
    {
        $this->user = $user;
        $this->identProviderId = $IdpId;
    }

    public function unsetSessionUser()
    {
        $this->user = null;
        $this->identProviderId = null;
    }

    public function hasSessionUser()
    {
        return $this->user !== null;
    }

    /**
     * @return AuthUser|null
     */
    public function getSessionUser()
    {
        return $this->user;
    }

    public function getIdentityProviderId()
    {
        return $this->identProviderId;
    }
}

