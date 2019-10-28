<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

use Jelix\Authentication\Core\IdentityProviderInterface;
use Jelix\Authentication\LoginPass\Manager;

class loginpassIdentityProvider implements IdentityProviderInterface {

    /**
     * @var Manager
     */
    protected $manager;

    public function __construct(array $options) {
        $backends = isset($options['backends']) && $options['backends'] != '' ? $options['backends'] : array('inifile');
        $this->manager = new Manager($backends, \jApp::config());
    }

    /**
     * @inheritDoc
     */
    public function getId() {
        return 'loginpass';
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
        $zp = array(
            'login' => $request->getParam('login'),
            'failed' => $request->getParam('failed')
        );
        return \jZone::get('authloginpass~loginform', $zp);
    }

    /**
     * @inheritDoc
     */
    public function checkSessionValidity ($request, $authUser, $authRequired) {
        if ($authRequired && ! $authUser) {
            throw new \jHttp401UnauthorizedException(\jLocale::get('authcore~auth.error.not.authenticated'));
        }
        return null;
    }

    /**
     * @return Manager
     */
    public function getManager() {
        return $this->manager;
    }
}
