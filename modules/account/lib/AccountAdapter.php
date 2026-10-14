<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2026 Laurent Jouanneau
 * @link      https://jelix.org
 * @license   MIT
 */

namespace Jelix\Authentication\Account;

use Jelix\Authentication\Core\AccountAdapterInterface;

class AccountAdapter implements AccountAdapterInterface
{
    public function getSupportedAccountClaims(): array
    {
        $mapping = AccountServices::getClaimMapping();
        return array_values($mapping->getMapping());
    }
}
