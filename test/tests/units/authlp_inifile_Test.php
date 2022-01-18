<?php
/**
 * @author      laurent Jouanneau
 * @copyright   2022 laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     MIT
 */

require_once('/jelixapp/modules/authloginpass/plugins/authlp/inifile/inifile.authlp.php');

/**
 * Tests ldap backend for the authloginpass idp
 */
class authlp_inifile_Test extends \Jelix\UnitTests\UnitTestCase
{

    protected function getIniBackend($iniFile)
    {
        $config = array(
            'inifile' => __DIR__.'/loginpassAssets/'.$iniFile
        );
        $authIni = new inifileBackend($config);
        return $authIni;
    }

    protected function getModifiedIniBackend($iniFile, $delete = false)
    {
        $file = __DIR__.'/../../testapp/temp/'.$iniFile;
        if (!file_exists($file) || $delete) {
            copy(__DIR__.'/loginpassAssets/test1.ini', $file);
        }

        $config = array(
            'inifile' => $file
        );
        $authIni = new inifileBackend($config);
        return $authIni;
    }

    function testUserExists()
    {
        $authIni = $this->getIniBackend('test1.ini');
        $this->assertTrue($authIni->userExists('admin'));
        $this->assertTrue($authIni->userExists('john'));
        $this->assertFalse($authIni->userExists('johnny'));
    }

    function testVerifyPassword()
    {
        $authIni = $this->getIniBackend('test1.ini');
        $user = $authIni->verifyAuthentication('john', 'passjohn');
        $this->assertIsObject($user);
        $this->assertEquals('John Doe' , $user->getName());
        $this->assertEquals('john@jelix.org' , $user->getEmail());
    }

    function testVerifyPasswordWithSessionAttributes()
    {
        $config = array(
            'inifile' => __DIR__.'/loginpassAssets/test1.ini',
            'sessionAttributes' => 'role'
        );
        $authIni = new inifileBackend($config);
        $user = $authIni->verifyAuthentication('john', 'passjohn');
        $this->assertIsObject($user);
        $this->assertEquals('John Doe' , $user->getName());
        $this->assertEquals('john@jelix.org' , $user->getEmail());
        $this->assertEquals('' , $user->getAttribute('role'));
        $this->assertEquals('' , $user->getAttribute('birthdayDate'));

        $user = $authIni->verifyAuthentication('jane', 'passjane');
        $this->assertIsObject($user);
        $this->assertEquals('Jane Doe' , $user->getName());
        $this->assertEquals('jane@jelix.org' , $user->getEmail());
        $this->assertEquals('ADMIN' , $user->getAttribute('role'));
        $this->assertEquals('' , $user->getAttribute('birthdayDate'));
    }

    function testVerifyPasswordWithAllSessionAttributes()
    {
        $config = array(
            'inifile' => __DIR__.'/loginpassAssets/test1.ini',
            'sessionAttributes' => 'ALL'
        );
        $authIni = new inifileBackend($config);
        $user = $authIni->verifyAuthentication('john', 'passjohn');
        $this->assertIsObject($user);
        $this->assertEquals('John Doe' , $user->getName());
        $this->assertEquals('john@jelix.org' , $user->getEmail());
        $this->assertEquals('' , $user->getAttribute('role'));
        $this->assertEquals('' , $user->getAttribute('birthdayDate'));

        $user = $authIni->verifyAuthentication('jane', 'passjane');
        $this->assertIsObject($user);
        $this->assertEquals('Jane Doe' , $user->getName());
        $this->assertEquals('jane@jelix.org' , $user->getEmail());
        $this->assertEquals('ADMIN' , $user->getAttribute('role'));
        $this->assertEquals('1990-01-01' , $user->getAttribute('birthdayDate'));
    }


    function testCreateUser()
    {

        $authIni = $this->getModifiedIniBackend('test_modified.ini', true);
        $this->assertTrue($authIni->createUser('robert', 'passrobert', 'robert@tests.jelix', 'Robert Dupont'));
        $this->assertTrue($authIni->userExists('robert'));
        $user = $authIni->verifyAuthentication('robert', 'passrobert');
        $this->assertIsObject($user);
        $this->assertEquals('Robert Dupont' , $user->getName());
        $this->assertEquals('robert@tests.jelix' , $user->getEmail());
    }

    /**
     * @depends testCreateUser
     */
    function testChangePassword() {
        $authIni = $this->getModifiedIniBackend('test_modified.ini');
        $this->assertTrue($authIni->changePassword('robert', 'passrobert2'));
        $this->assertTrue($authIni->userExists('robert'));
        $this->assertFalse($authIni->verifyAuthentication('robert', 'passrobert'));
        $user = $authIni->verifyAuthentication('robert', 'passrobert2');
        $this->assertIsObject($user);
        $this->assertEquals('Robert Dupont' , $user->getName());
        $this->assertEquals('robert@tests.jelix' , $user->getEmail());
    }

    /**
     * @depends testChangePassword
     */
    function testDeleteUser()
    {
        $authIni = $this->getModifiedIniBackend('test_modified.ini');
        $user = $authIni->deleteUser('robert');
        $this->assertIsObject($user);
        $this->assertTrue($authIni->deleteUser('robert'));
        $this->assertEquals('Robert Dupont' , $user->getName());
        $this->assertEquals('robert@tests.jelix' , $user->getEmail());
        $this->assertFalse($authIni->userExists('robert'));
    }


}