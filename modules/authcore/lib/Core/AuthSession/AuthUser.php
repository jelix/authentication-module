<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019-2026 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core\AuthSession;

use Jelix\Authentication\Core\IdentityAttributes;

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
     * Attributes should contain data like email, name, login.
     * These information should be stored at the keys indicated into the
     * IdentityAttributes constants.
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
    function getName()
    {
        if ($this->account) {
            $displayName = $this->account->getRealName();
            if (trim($displayName)) {
                return $displayName;
            }
        }

        if (isset($this->attributes[IdentityAttributes::DisplayName]) && $this->attributes[IdentityAttributes::DisplayName] !== '') {
            return $this->attributes[IdentityAttributes::DisplayName];
        }

        $first = $this->attributes[IdentityAttributes::FirstName] ?? '';
        $last = $this->attributes[IdentityAttributes::LastName] ?? '';
        $combined = trim($first . ' ' . $last);
        if ($combined !== '') {
            return $combined;
        }

        return $this->getLogin();
    }

    /**
     * The email of the user
     * @return string
     */
    function getEmail() {
        if (isset($this->attributes[IdentityAttributes::Email]) && $this->attributes[IdentityAttributes::Email] !== '') {
            return $this->attributes[IdentityAttributes::Email];
        }
        return '';
    }

    /**
     * The username/login (technical name) of the user
     *
     * @return string
     */
    function getLogin() {
        if (isset($this->attributes[IdentityAttributes::UserName]) && $this->attributes[IdentityAttributes::UserName] !== '') {
            return $this->attributes[IdentityAttributes::UserName];
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
     * Any attributes except the username and the email can be set.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    function setAttribute($name, $value) {
        if (!in_array($name, array(IdentityAttributes::UserName, IdentityAttributes::Email))) {
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

    static function getDefaultSupportedAccountClaims()
    {
        return [
            IdentityAttributes::UserName,
            IdentityAttributes::DisplayName,
            IdentityAttributes::FirstName,
            IdentityAttributes::LastName,
            IdentityAttributes::Email
        ];
    }
}
