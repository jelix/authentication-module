<?php

namespace Jelix\Authentication\IdpAdmin;

use jAuthentication;
use Jelix\Authentication\Core\IdentityProviderInterface;
use jEvent;

class IdpFinder
{
    public function findAllIDP()
    {
        // use event to find all existing idp plugin
        $allIdpResponse = jEvent::notify('declareIDPlugin')->getResponse();
        $authManager = jAuthentication::manager();
        $enabledIdps = $authManager->getIdpList();
        $enabledIdpNames = array_map(function (IdentityProviderInterface $idp) {return $idp->getId();}, $enabledIdps);
        $allIdpName = [];
        foreach($allIdpResponse as $idpInfo) {
            if (array_key_exists('pluginName', $idpInfo)) {
                $pluginName = $idpInfo['pluginName'];
                $idpEnabled = (false !== array_search($pluginName, $enabledIdpNames));
                $allIdpName[] = [$pluginName, $idpEnabled];
            }

        }

        return $allIdpName;
    }
}
