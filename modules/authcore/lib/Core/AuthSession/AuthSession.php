<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core\AuthSession;

/**
 * Manage the authenticated session
 *
 */
class AuthSession {

    /**
     * @var AuthSessionHandlerInterface
     */
    protected $handler;

    public function __construct(AuthSessionHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param AuthUser $user
     * @param string $IPid
     * @return bool false if the authenticated user is not allowed to use the application
     */
    public function setSessionUser(AuthUser $user, $IdpId)
    {
        $event = \jEvent::notify('AuthenticationCanUseApp', array(
            'user' => $user,
            'identProviderId' => $IdpId
        ));
        if (false === $event->allResponsesByKeyAreTrue('canUseApp')) {
            return false;
        }

        $this->handler->setSessionUser($user, $IdpId);

        \jEvent::notify('AuthenticationLogin', array(
            'user' => $user,
            'identProviderId' => $IdpId
        ));

        return true;
    }

    public function unsetSessionUser()
    {
        if ($this->handler->hasSessionUser()) {
            $user = $this->handler->getSessionUser();
            $this->handler->unsetSessionUser();
            \jEvent::notify('AuthenticationLogout', array('user' => $user));
        }
        else {
            $this->handler->unsetSessionUser();
        }
    }

    public function hasSessionUser()
    {
        return $this->handler->hasSessionUser();
    }

    /**
     * @return AuthUser|null
     */
    public function getSessionUser()
    {
        return $this->handler->getSessionUser();
    }

    public function getIdentityProviderId()
    {
        return $this->handler->getIdentityProviderId();
    }
}
