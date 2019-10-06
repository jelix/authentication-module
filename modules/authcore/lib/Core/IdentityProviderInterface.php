<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core;


/**
 * interface for Identity provider
 */
interface IdentityProviderInterface {

    /**
     * the identifiant of the identity provider
     *
     * Should be specific to the identity provider, and used to identify the
     * identity provider
     * @return string
     */
    public function getId();

    /**
     * give the url to use to authenticate the user
     *
     * @return string
     */
    public function getLoginUrl();

    /**
     * give the url to use to logout the user
     *
     * @return string
     */
    public function getLogoutUrl();

    /**
     * Give the html to display in a login page.
     *
     * Note that there may have as html content as activated identity providers
     *
     * @return string html content
     */
    public function getHtmlLoginForm(\jRequest $request);
}