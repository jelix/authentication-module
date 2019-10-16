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

    protected $name;

    protected $attributes = array();

    /**
     * SessionUser constructor.
     * @param string $userId
     * @param string $userName
     * @param array $attributes
     */
    function __construct($userId, $name, array $attributes) {
        $this->attributes = $attributes;
        $this->userId = $userId;
        $this->name = $name ?: $userId;
    }

    function getUserId() {
        return $this->userId;
    }

    function getName() {
        return $this->name;
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
}
