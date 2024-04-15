<?php
/**
 * @author       Laurent Jouanneau <laurent@jelix.org>
 * @copyright    2024 Laurent Jouanneau
 *
 * @link         https://jelix.org
 * @licence      MIT
 */
namespace Jelix\Authentication\LoginPass;

use Jelix\Authentication\Core\AuthSession\AuthUser;

/**
 * Event sent when a password change is requested
 *
 * A listener can allow or deny this change.
 */
class AuthLPCanResetPasswordEvent extends \jEvent
{

    public function __construct(string $idpId, AuthUser $authUser)
    {
        parent::__construct('AuthLPCanResetPassword',
            array(
                'user' => $authUser,
                'idpId' => $idpId
            ));
    }

    /**
     * @return string
     */
    public function getIdpId()
    {
        return $this->_params['idpId'];
    }

    /**
     * @return AuthUser
     */
    public function getUser()
    {
        return $this->_params['user'];
    }

    /**
     * Allow the user to reset his password
     * @return void
     */
    public function allow()
    {
        $this->add(['allowed' => true ]);
    }

    /**
     * Disallow the user to reset his password
     */
    public function deny()
    {
        $this->add(['allowed' => false ]);
    }

    /**
     * @return bool
     */
    public function isResetPasswordAllowed()
    {
        return $this->allResponsesByKeyAreTrue('allowed');
    }

}