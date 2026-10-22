<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2026 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Account;

use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\IdentityAttributes;

/**
 * Maps identity attribute keys (from IdentityAttributes) to account storage fields.
 *
 * The mapping is configured via the [accounts:claim-mapping] INI section.
 * Default mapping covers the standard account DAO fields.
 */
class ClaimMapping
{
    protected $mapping = [];

    protected static $nativeMapping = [
        'username' => IdentityAttributes::UserName,
        'displayname' => IdentityAttributes::DisplayName,
        'firstname' => IdentityAttributes::FirstName,
        'lastname' => IdentityAttributes::LastName,
        'email' => IdentityAttributes::Email,
    ];

    /**
     * @param array $configuredMapping mapping from account field names to identity attribute keys,
     *                                 complements the native mapping
     */
    public function __construct(array $configuredMapping = [])
    {
        $this->mapping = array_merge($configuredMapping, self::$nativeMapping);
    }

    /**
     * Returns the identity attribute key for a given account field.
     *
     * @param string $accountField
     * @return string|null
     */
    public function getIdentityAttributeKey(string $accountField): ?string
    {
        return $this->mapping[$accountField] ?? null;
    }

    /**
     * Given an AuthUser, extract mapped values as account_field => value.
     *
     * @param AuthUser $user
     * @return array associative array of account_field => value
     */
    public function mapAuthUserToAccountFields(AuthUser $user): array
    {
        $result = [];
        foreach ($this->mapping as $accountField => $identityKey) {
            $value = $user->getAttribute($identityKey);
            if ($value !== null) {
                $result[$accountField] = $value;
            }
        }

        if (!isset($result['username'])) {
            $result['username'] = $user->getLogin();
        }

        if (!isset($result['email'])) {
            $result['email'] = $user->getEmail();
        }

        if (!isset($result['displayname'])) {
            $result['displayname'] = $user->getName();
        }

        return $result;
    }

    /**
     * @return array the full mapping (account_field => identity_attribute_key)
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }
}
