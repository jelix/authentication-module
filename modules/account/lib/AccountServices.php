<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2026 Laurent Jouanneau
 * @link      https://jelix.org
 * @license   MIT
 */

namespace Jelix\Authentication\Account;

/**
 * provide convenience methods to instancy some classes for the account module.
 */
class AccountServices
{

    static protected ?ClaimMapping $claimMapping = null;


    static public function getClaimMapping(): ClaimMapping
    {
        if (self::$claimMapping === null) {
            $configSection = 'accounts:claim-mapping';
            $configData = [];
            if (property_exists(\jApp::config(), $configSection) &&
                is_array(\jApp::config()->$configSection)
            ) {
                $configData = \jApp::config()->$configSection;
            }
            self::$claimMapping = new ClaimMapping($configData);
        }
        return self::$claimMapping;
    }


}
