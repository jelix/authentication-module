<?php

namespace Jelix\Authentication\LoginPass;

use jAuthentication;
use jEventListener;
use jUrl;
use jLocale;
use Jelix\Authentication\Account\ProfileViewPageEvent;
use Jelix\Authentication\LoginPass\Config as LoginPassConfig;

class ProfileEventListener extends jEventListener
{
    public function onProfileViewPageEvent(ProfileViewPageEvent $event)
    {
        $session = jAuthentication::session();
        $idp = $session->getIdentityProviderId();
        if($idp == 'loginpass') {
            $loginPassConfig = new LoginPassConfig(\jApp::config());
            // does login pass conf allow password change ?
            if($loginPassConfig->isPasswordChangeEnabled()) {
                /** @var \loginpassIdentityProvider $idp */
                $idp = jAuthentication::manager()->getIdpById('loginpass');
                /** @var \Jelix\Authentication\LoginPass\Manager $lpManager */
                $lpManager = $idp->getManager();
                $login = jAuthentication::getCurrentUser()->getLogin();
                // check if the backend allow passwordChange
                if($lpManager->canChangePassword($login)) {
                    $event->addContent('<a href="'. jUrl::get('authloginpass~passwordEdit:show').'" class="btn btn-primary">'.jLocale::get('authloginpass~password.btn.password.edit').'</a>', 6);
                }
            }
        }
    }
}
