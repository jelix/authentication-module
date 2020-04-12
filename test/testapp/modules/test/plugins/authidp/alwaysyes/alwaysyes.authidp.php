<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

use Jelix\Authentication\Core\IdentityProviderInterface;

class AlwaysYesIdentityProvider implements IdentityProviderInterface {


    public function __construct(array $options) {

    }

    /**
     * @inheritDoc
     */
    public function getId() {
        return 'alwaysyes';
    }

    /**
     * @inheritDoc
     */
    public function getLoginUrl() {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getLogoutUrl() {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getHtmlLoginForm(\jRequest $request) {
        $tpl = new \jTpl();
        return $tpl->fetch('test~alwaysyesform');
    }

    /**
     *
     * @inheritDoc
     */
    public function checkSessionValidity ($request, $authUser, $authRequired) {
        if ($authRequired && !$authUser) {
            throw new \jHttp401UnauthorizedException();
        }
        return null;
    }
}
