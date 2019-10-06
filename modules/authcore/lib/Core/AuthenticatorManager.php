<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core;

/**
 *
 */
class AuthenticatorManager
{

    /**
     * @var IdentityProviderInterface[]
     */
    protected $idpList = array();

    /**
     * AuthenticatorManager constructor.
     * @param IdentityProviderInterface[] $idpList
     */
    public function __construct($idpList)
    {
        foreach($idpList as $idp) {
            $this->idpList[$idp->getId()] = $idp;
        }
    }


    /**
     * @param string $id
     * @return IdentityProviderInterface|null
     */
    public function getIdpById($id) {
        if (isset($this->idpList[$id])) {
            return $this->idpList[$id];
        }
        return null;
    }

    /**
     * @return IdentityProviderInterface[]
     */
    public function getIdpList() {
        return $this->idpList;
    }

    /**
     * @return IdentityProviderInterface[]
     */
    public function getMainIdp() {
        return reset($this->idpList);
    }


}
