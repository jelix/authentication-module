<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019-2024 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\LoginPass;

use Jelix\Authentication\Core\AuthSession\AuthUser;

interface BackendPluginInterface
{
    /** @var int the backend does not implement specific features */
    const FEATURE_NONE = 0;

    /**
     * If the plugin has this feature, it should provide a "status" attribute
     * that have one of AuthUser::STATUS_* values
     *
     * @var int password of a user can be changed
     */
    const FEATURE_CHANGE_PASSWORD = 1;

    /** @var int the backend can create user */
    const FEATURE_CREATE_USER = 2;

    /** @var int the backend can delete user */
    const FEATURE_DELETE_USER = 4;


    /**
     * BackendPluginInterface constructor.
     * @param array $params configuration parameters
     */
    public function __construct($params);

    /**
     * @return string a label to display
     */
    public function getLabel();

    /**
     * @param string $key a key given by loginpass, used internally
     */
    public function setRegisterKey($key);

    /**
     * @return string the key given by loginpass
     */
    public function getRegisterKey();

    /**
     * @return array configuration parameters
     */
    public function getConfiguration();

    /**
     * @return integer a combination of FEATURE_* constants
     */
    public function getFeatures();

    /**
     * @param int $feature one of FEATURE_* constants or a combination of them
     * @return boolean true if the backend has the given feature
     */
    public function hasFeature($feature);

    /**
     * Create a user into the backend
     *
     * @param string $login
     * @param string $password
     * @param string $email
     * @param string $name
     * @return boolean
     */
    public function createUser($login, $password, $email, $name = '');

    /**
     * Delete a user from the backend
     *
     * @param string $login
     * @return bool|AuthUser the AuthUser object corresponding to the deleted user,
     *   true if already deleted, or false if the user has not been deleted
     */
    public function deleteUser($login);

    /**
     * @param string $login login in lowercase, as stored into the database
     * @param string $newpassword
     * @return boolean
     */
    public function changePassword($login, $newpassword);

    /**
     * @param string $login the login as given by the user
     * @param string $password
     * @return false|AuthUser
     */
    public function verifyAuthentication($login, $password);

    /**
     * @param string $login
     * @return boolean true if a user with this login exists
     */
    public function userExists($login);

    /**
     * @param string $email
     * @return string|false the login if a user exists with the given email, or  false if not found
     */
    public function userWithEmailExists($email);

    /**
     * Gets an user by its login
     * 
     * @param string $login The user to get
     * @return AuthUser The user corresponding to $login, null if none.
     */
    public function getUser($login);

    /**
     * Updates user informations
     * 
     * @param string $login The user's login
     * @param array $attributes The attributes to modify
     * 
     * @return void
     */
    public function updateUser($login, $attributes);
}
