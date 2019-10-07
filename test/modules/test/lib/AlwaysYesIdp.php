<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */
namespace TestAuth;

use Jelix\Authentication\Core\IdentityProviderInterface;

class AlwaysYesIdp implements IdentityProviderInterface {


    /**
     * the identifiant of the identity provider
     *
     * Should be specific to the identity provider, and used to identify the
     * identity provider
     * @return string
     */
    public function getId() {
        return 'alwaysyes';
    }

    /**
     * give the url to use to authenticate the user
     *
     * @return string
     */
    public function getLoginUrl() {
        return '';
    }

    /**
     * give the url to use to logout the user
     *
     * @return string
     */
    public function getLogoutUrl() {
        return '';
    }

    /**
     * Give the html to display in a login page.
     *
     * Note that there may have as html content as activated identity providers
     *
     * @return string html content
     */
    public function getHtmlLoginForm(\jRequest $request) {
        $tpl = new \jTpl();
        return $tpl->fetch('test~alwaysyesform');
    }

}