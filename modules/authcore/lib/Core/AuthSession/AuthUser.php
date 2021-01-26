<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core\AuthSession;

/**
 * Properties about a user in a authentication session
 *
 * This object contains user attributes.
 *
 * Attributes are supposed to be set by any module during the login event,
 * for example, by a module managing accounts.
 */
class AuthUser
{

    protected $userId;

    protected $attributes = array();

    const ATTR_LOGIN = 'login';

    const ATTR_NAME = 'username';

    const ATTR_EMAIL = 'email';

    const STATUS_PWD_CHANGED = 3;
    const STATUS_MAIL_CHANGED = 2;
    const STATUS_VALID = 1;
    const STATUS_NEW = 0;
    const STATUS_DEACTIVATED = -1;
    const STATUS_DELETED = -2;

    /**
     * SessionUser constructor.
     * @param string $userId
     * @param string $userName
     * @param array $attributes
     */
    function __construct($userId, array $attributes) {
        $this->attributes = $attributes;
        $this->userId = $userId;
    }

    function getUserId() {
        return $this->userId;
    }

    function getName() {
        if (isset($this->attributes[self::ATTR_NAME]) && $this->attributes[self::ATTR_NAME] !== '') {
            return $this->attributes[self::ATTR_NAME];
        }
        return $this->getLogin();
    }

    function getEmail() {
        if (isset($this->attributes[self::ATTR_EMAIL]) && $this->attributes[self::ATTR_EMAIL] !== '') {
            return $this->attributes[self::ATTR_EMAIL];
        }
        return '';
    }

    function getLogin() {
        if (isset($this->attributes[self::ATTR_LOGIN]) && $this->attributes[self::ATTR_LOGIN] !== '') {
            return $this->attributes[self::ATTR_LOGIN];
        }
        return $this->userId;
    }

    function getAttribute($name) {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return null;
    }

    function getAttributes() {
        return $this->attributes;
    }

    function setAttribute($name, $value) {
        if (!in_array($name, array(self::ATTR_LOGIN, self::ATTR_EMAIL))) {
            $this->attributes[$name] = $value;
        }
    }
}
