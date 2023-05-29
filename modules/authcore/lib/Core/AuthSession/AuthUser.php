<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core\AuthSession;

/**
 * Properties about a user in an authentication session
 *
 * This object contains user attributes.
 *
 * Attributes are supposed to be set by any module during the login event,
 * for example, by a module managing accounts.
 */
class AuthUser
{
    /**
     * @var string the user id
     */
    protected $userId;

    protected $attributes = array();

    /**
     * @var UserAccountInterface
     */
    protected $account;

    /**
     * Name of the attribute containing the username/login (technical name) of the user
     */
    const ATTR_LOGIN = 'login';

    /**
     * Name of the attribute containing the real name/ display name of the user
     */
    const ATTR_NAME = 'realname';

    /**
     * Name of the attribute containing the e-mail of the user
     */
    const ATTR_EMAIL = 'email';

    const STATUS_PWD_CHANGED = 3;
    const STATUS_MAIL_CHANGED = 2;
    const STATUS_VALID = 1;
    const STATUS_NEW = 0;
    const STATUS_DEACTIVATED = -1;
    const STATUS_DELETED = -2;

    /**
     * AuthUser constructor.
     *
     * The technical id of the user and user's attributes should be given.
     * Attributes should contain data like email, real name, login.
     * These information should be stored at the keys indicated into the ATTR_* constants.
     *
     * @param string $userId Technical id of the user from the authentication provider
     * @param array $attributes list of attributes
     */
    function __construct($userId, array $attributes) {
        $this->attributes = $attributes;
        $this->userId = $userId;
    }

    /**
     * Technical id of the user from the authentication provider
     *
     * @return string
     */
    function getUserId() {
        return $this->userId;
    }

    /**
     * The display name / real name of the user
     *
     * @return string
     */
    function getName() {
        if (isset($this->attributes[self::ATTR_NAME]) && $this->attributes[self::ATTR_NAME] !== '') {
            return $this->attributes[self::ATTR_NAME];
        }
        return $this->getLogin();
    }

    /**
     * The email of the user
     * @return string
     */
    function getEmail() {
        if (isset($this->attributes[self::ATTR_EMAIL]) && $this->attributes[self::ATTR_EMAIL] !== '') {
            return $this->attributes[self::ATTR_EMAIL];
        }
        return '';
    }

    /**
     * The username/login (technical name) of the user
     *
     * @return string
     */
    function getLogin() {
        if (isset($this->attributes[self::ATTR_LOGIN]) && $this->attributes[self::ATTR_LOGIN] !== '') {
            return $this->attributes[self::ATTR_LOGIN];
        }
        return $this->userId;
    }

    /**
     * @param string $name the attribute name
     * @return mixed|null value of the attribute
     */
    function getAttribute($name) {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return null;
    }

    /**
     * @return array all attributes
     */
    function getAttributes() {
        return $this->attributes;
    }

    /**
     * Set a specific attribute.
     *
     * Any attributes except the email and the login can be set.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    function setAttribute($name, $value) {
        if (!in_array($name, array(self::ATTR_LOGIN, self::ATTR_EMAIL))) {
            $this->attributes[$name] = $value;
        }
    }

    /**
     * Set the account corresponding to the authenticated user.
     *
     * This method should be called during the authentication process.
     *
     * @param UserAccountInterface $account
     * @return void
     */
    public function setAccount(UserAccountInterface $account)
    {
        $this->account = $account;
    }

    /**
     * @return UserAccountInterface|null the account corresponding to the user.
     */
    public function getAccount()
    {
        return $this->account;
    }
}
