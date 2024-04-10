<?php
/**
 * @author      laurent Jouanneau
 * @copyright   2019-2024 laurent Jouanneau
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
        'hostname'      =>  'ldap.jelix',
        'tlsMode'       => 'starttls',
        'port'          =>  389,
        //'tlsMode'       => 'ldaps',
        //'port'          =>  636,
        'adminUserDn'      =>  'cn=admin,dc=tests,dc=jelix',
        'adminPassword'      =>  'passjelix',
        'searchUserBaseDN' => 'ou=people,dc=tests,dc=jelix',
        'searchUserFilter' => "(&(objectClass=inetOrgPerson)(uid=%%LOGIN%%))",
        'searchUserByEmailFilter' => "(&(objectClass=inetOrgPerson)(mail=%%EMAIL%%))",
        'bindUserDN' => "uid=%?%,ou=people,dc=tests,dc=jelix",
        'newUserDN' => "uid=%%LOGIN%%,ou=people,dc=tests,dc=jelix",
        'newUserLdapAttributes' => "objectClass:inetOrgPerson,userPassword:%%PASSWORD%%,cn:%%REALNAME%%,sn:%%REALNAME%%",
        'searchAttributes' => "uid:login,displayName:realname,mail:email",
    );

    function testUserExists() {
        $config = $this->ldapProfile;
        $config['tlsMode'] = '';
        $ldap = new ldapBackend($config, array());

        $this->assertTrue($ldap->userExists('john'));
        $this->assertFalse($ldap->userExists('johnny'));
    }

    function testUserExistsStartTls() {
        $config = $this->ldapProfile;
        $config['tlsMode'] = 'starttls';
        $ldap = new ldapBackend($config, array());

        $this->assertTrue($ldap->userExists('john'));
        $this->assertFalse($ldap->userExists('johnny'));
    }

    function testUserExistsLdaps() {
        $config = $this->ldapProfile;
        $config['tlsMode'] = 'ldaps';
        $config['port'] = 636;

        $ldap = new ldapBackend($config, array());

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

    function testCreateUser() {
        $ldap = new ldapBackend($this->ldapProfile, array());
        $this->assertTrue($ldap->createUser('testldap', 'passrobert', 'robert@tests.jelix', 'Robert Dupont'));
        $this->assertTrue($ldap->userExists('testldap'));
        $user = $ldap->verifyAuthentication('testldap', 'passrobert');
        $this->assertIsObject($user);
        $this->assertEquals('Robert Dupont' , $user->getName());
        $this->assertEquals('robert@tests.jelix' , $user->getEmail());
    }

    /**
     * @depends testCreateUser
     */
    function testChangePassword() {
        $ldap = new ldapBackend($this->ldapProfile, array());
        $this->assertTrue($ldap->changePassword('testldap', 'passrobert2'));
        $this->assertTrue($ldap->userExists('testldap'));
        $this->assertFalse($ldap->verifyAuthentication('testldap', 'passrobert'));
        $user = $ldap->verifyAuthentication('testldap', 'passrobert2');
        $this->assertIsObject($user);
        $this->assertEquals('Robert Dupont' , $user->getName());
        $this->assertEquals('robert@tests.jelix' , $user->getEmail());
    }

    /**
     * @throws jException
     * @depends testChangePassword
     */
    function testChangeHashedPassword() {
        $config = $this->ldapProfile;
        $config['passwordLdapHashAlgo'] = 'SSHA';
        $ldap = new ldapBackend($config, array());
        $this->assertTrue($ldap->changePassword('testldap', 'passroberthashed'));
        $this->assertTrue($ldap->userExists('testldap'));
        $this->assertFalse($ldap->verifyAuthentication('testldap', 'passrobert'));
        $user = $ldap->verifyAuthentication('testldap', 'passroberthashed');
        $this->assertIsObject($user);
        $this->assertEquals('Robert Dupont' , $user->getName());
        $this->assertEquals('robert@tests.jelix' , $user->getEmail());
    }

    /**
     * @depends testChangeHashedPassword
     */
    function testGetUserByEmail()
    {
        $ldap = new ldapBackend($this->ldapProfile, array());
        $this->assertEquals('testldap', $ldap->userWithEmailExists('robert@tests.jelix'));
        $this->assertFalse($ldap->userWithEmailExists('unknown@tests.jelix'));
    }

    /**
     * @depends testChangePassword
     */
    function testDeleteUser() {
        $ldap = new ldapBackend($this->ldapProfile, array());
        $user = $ldap->deleteUser('testldap');
        $this->assertIsObject($user);
        $this->assertTrue($ldap->deleteUser('testldap'));
        $this->assertEquals('Robert Dupont' , $user->getName());
        $this->assertEquals('robert@tests.jelix' , $user->getEmail());
        $this->assertFalse($ldap->userExists('testldap'));
    }
}
