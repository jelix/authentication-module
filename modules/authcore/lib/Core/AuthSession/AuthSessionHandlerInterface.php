<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core\AuthSession;


interface AuthSessionHandlerInterface
{

    public function setSessionUser(AuthUser $user, $IPid);

    public function unsetSessionUser();

    public function hasSessionUser();

    /**
     * @return AuthUser|null
     */
    public function getSessionUser();

    public function getIdentityProviderId();

}