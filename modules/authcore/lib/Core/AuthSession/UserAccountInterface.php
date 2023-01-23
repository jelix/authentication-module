<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core\AuthSession;

/**
 * interface that should be implemented by objects representing an account
 * that are given by listeners responding to the `GetAccountEvent` during
 * authentication.
 *
 * Object implementing this interface are stored into the session, so it should
 * not contain too much data and should be serializable.
 */
interface UserAccountInterface
{
    /**
     * @return string an account id
     */
    public function getAccountId();

    /**
     * @return string a username
     */
    public function getUserName();

    /**
     * @return string the user email
     */
    public function getEmail();
}