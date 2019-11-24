<?php
/**
 * @author      laurent Jouanneau
 * @copyright   2019 laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     MIT
 */

require_once('/jelixapp/modules/authloginpass/plugins/authlp/ldap/ldap.authlp.php');

/**
 * Tests ldap backend for the authloginpass idp
 */
class authlp_ldap_auth_Test extends \Jelix\UnitTests\UnitTestCase {

    protected $ldapPort = 389;

    protected $ldapTlsMode = '';

    protected $ldapProfileName = 'lpldap';

    protected $ldapProfile = array(
        'hostname'      =>  'openldap',
        'tlsMode'       => '',
        'port'          =>  389,
        'adminUserDn'      =>  'cn=admin,dc=tests,dc=jelix',
        'adminPassword'      =>  'passjelix',
        'searchUserBaseDN' => 'ou=people,dc=tests,dc=jelix',
        'searchUserFilter' => "(&(objectClass=inetOrgPerson)(uid=%%LOGIN%%))",
        'bindUserDN' => "uid=%?%,ou=people,dc=tests,dc=jelix",
        'searchAttributes'=>"uid:login,displayName:username,mail:email",
    );

    function testUserExists() {
        $ldap = new ldapBackend($this->ldapProfile, array());

        $this->assertTrue($ldap->userExists('john'));
        $this->assertFalse($ldap->userExists('johnny'));
    }

    function testVerifyPassword() {
        $ldap = new ldapBackend($this->ldapProfile, array());
        $user = $ldap->verifyAuthentication('john', 'passjohn');
        $this->assertIsObject($user);
        $this->assertEquals('John Doe' , $user->getName());
        $this->assertEquals('john@jelix.org' , $user->getEmail());
    }


}
