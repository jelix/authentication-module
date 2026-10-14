<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2026 Laurent Jouanneau
 * @link      https://jelix.org
 * @license   MIT
 */
namespace Jelix\Authentication\Core;

interface AccountAdapterInterface
{
    /**
     * @return array the list of claims supported by the account module
     */
    public function getSupportedAccountClaims(): array;
}
