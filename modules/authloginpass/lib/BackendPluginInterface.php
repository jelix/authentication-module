<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\LoginPass;

interface BackendPluginInterface
{
    /** @var int password of a user can be changed */
    const FEATURE_CHANGE_PASSWORD = 1;

    /** @var int the backend does not implement specific features */
    const FEATURE_NONE = 0;

    const VERIF_AUTH_BAD = 0;
    const VERIF_AUTH_OK = 1;

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
     * @param string $login login in lowercase, as stored into the database
     * @param string $newpassword
     * @return boolean
     */
    public function changePassword($login, $newpassword);

    /**
     * @param string $login the login as given by the user
     * @param string $password
     * @return int one of VERIF_AUTH_* const
     */
    public function verifyAuthentication($login, $password);

    /**
     * @param string $login
     * @return boolean true if a user with this login exists
     */
    public function userExists($login);
}
