<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @licence MIT
 */

use Jelix\Authentication\Core\AuthSession\AuthUser;

class alwaysyesCtrl extends jController
{
    /**
     *
     */
    function signin()
    {
        $user = new AuthUser(
            'testuser',
            array(
                AuthUser::ATTR_NAME => 'User Test'
            )
        );
        $idp = jAuthentication::manager()->getIdpById('alwaysyes');
        jAuthentication::session()->setSessionUser($user, $idp);

        $workflow = jAuthentication::startAuthenticationWorkflow($user, $idp);
        $workflow->setFinalUrl(jUrl::get('test~default:index'));

        return $this->redirectToUrl($workflow->getNextAuthenticationUrl());
    }
}
