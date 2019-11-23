<?php
/**
 * @author      laurent Jouanneau
 * @copyright   2019 laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     MIT
 */


/**
 * Tests ldap backend for the authloginpass idp
 */
class authlp_ldap_Test extends \Jelix\UnitTests\UnitTestCase {

    protected $ldapPort = 389;

    protected $ldapTlsMode = '';

    protected $ldapProfileName = 'lpldap';

    function testNothing() {
        $this->assertTrue(true);
    }
}
