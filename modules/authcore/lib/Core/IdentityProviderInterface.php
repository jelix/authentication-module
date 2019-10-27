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


    public function __construct(array $options);

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

    /**
     * verify if the session is valid
     *
     * The identity provider should indicate by the returned value,
     * if the target action can be executed (the method must return null), or
     * if an other action should be executed (the method must return a jSelectorAct object),
     * in case of a lack of authentication.
     *
     * The identity provider can also throw a jHttp401UnauthorizedException exception
     * if it cannot provides an action to process the case of a lack of authentication.
     *
     * @param \jRequest $request
     * @param AuthSession\AuthUser|null $authUser the user identified into the session. null if there is no user
     * @param boolean $authRequired  true when the current action needs an authenticated user
     * @return \jSelectorAct|null return an action to redirect to, without error
     * @throws \jHttp401UnauthorizedException
     */
    public function checkSessionValidity ($request, $authUser, $authRequired);
}
