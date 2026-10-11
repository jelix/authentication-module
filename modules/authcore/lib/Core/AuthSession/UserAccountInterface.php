<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2026 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core\AuthSession;

/**
 * Interface that should be implemented by objects representing an account
 * It is given by an account workflow adapter during the authentication.
 *
 * The object implementing this interface is stored into the session, so it should
 * not contain too much data and should be serializable.
 */
interface UserAccountInterface
{
    /**
     * The account id is the internal id of the account
     *
     * It may be an integer (like an autoincremented column from an SQL table)
     * or a string if it is a username.
     *
     * The account id must be unique among all the accounts
     *
     * @return string an account id
     */
    public function getAccountId();

    /**
     * The username is the technical name of the user,
     *
     * It that can be used as a login name, or as a key into a SQL table or
     * any other data structure.
     * It may be equal to the account id if the account id is a string.
     *
     * The username must be unique among all the accounts
     *
     * @return string a username
     */
    public function getUserName();

    /**
     * The real name is the name displayed into the application.
     *
     * It may contain the firstname and the lastname, or a nickname
     * if the application allows nicknames etc..
     *
     * @return string the real name of the user
     */
    public function getRealName();

    /**
     * @return string the user email
     */
    public function getEmail();
}
