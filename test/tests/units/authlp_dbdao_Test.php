<?php
/**
 * @author      laurent Jouanneau
 * @copyright   2022 laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     MIT
 */

require_once('/jelixapp/modules/authloginpass/plugins/authlp/dbdao/dbdao.authlp.php');

/**
 * Tests dbdao backend for the authloginpass idp
 */
class authlp_dbdao_Test extends \Jelix\UnitTests\UnitTestCase
{

    protected function getBackend($delete = false)
    {

        if ($delete) {
            $db = jDb::getConnection('dbdaotest');
            $db->exec("DROP TABLE IF EXISTS auth_users");
            $db->exec("CREATE TABLE IF NOT EXISTS auth_users (
                user_id integer PRIMARY KEY  AUTOINCREMENT, 
                login varchar(50) NOT NULL, 
                password varchar(120) NOT NULL, 
                status integer NOT NULL, 
                email varchar(255) NOT NULL, 
                username varchar(255) DEFAULT NULL, 
                create_date datetime DEFAULT NULL, 
                attributes varchar DEFAULT NULL,
                role varchar(255) DEFAULT NULL, 
                birthday_date datetime DEFAULT NULL
            )");
            $db->exec("INSERT INTO auth_users (login, password, status, email, username, create_date, attributes, role, birthday_date)
                VALUES 
                ('john', '".password_hash('passjohn', PASSWORD_DEFAULT)."', 1, 'john@jelix.org', 'John Doe', date('Y-m-d'), '{}', '', ''),
                ('jane', '".password_hash('passjane', PASSWORD_DEFAULT)."', 1, 'jane@jelix.org', 'Jane Doe', date('Y-m-d'), '{}', 'ADMIN', '1990-01-01')
            ");
        }

        $config = array(
            'profile' => 'dbdaotest',
            'dao' => 'test~user'
        );
        $auth = new dbdaoBackend($config);
        return $auth;
    }

    protected function getModifiedIniBackend($iniFile, $delete = false)
    {
    }
    

    function testUserExists()
    {
        $authDb = $this->getBackend(true);
        $this->assertTrue($authDb->userExists('john'));
        $this->assertFalse($authDb->userExists('johnny'));
    }

    /**
     * @depends testUserExists
     */
    function testVerifyPassword()
    {
        $authDb = $this->getBackend();
        $user = $authDb->verifyAuthentication('john', 'passjohn');
        $this->assertIsObject($user);
        $this->assertEquals('John Doe' , $user->getName());
        $this->assertEquals('john@jelix.org' , $user->getEmail());
    }

    /**
     * @depends testVerifyPassword
     */
    function testVerifyPasswordWithSessionAttributes()
    {
        $config = array(
            'profile' => 'dbdaotest',
            'dao' => 'test~user',
            'sessionAttributes' => 'role'
        );
        $auth = new dbdaoBackend($config);
        $user = $auth->verifyAuthentication('john', 'passjohn');
        $this->assertIsObject($user);
        $this->assertEquals('John Doe' , $user->getName());
        $this->assertEquals('john@jelix.org' , $user->getEmail());
        $this->assertEquals('' , $user->getAttribute('role'));
        $this->assertEquals('' , $user->getAttribute('birthdayDate'));

        $user = $auth->verifyAuthentication('jane', 'passjane');
        $this->assertIsObject($user);
        $this->assertEquals('Jane Doe' , $user->getName());
        $this->assertEquals('jane@jelix.org' , $user->getEmail());
        $this->assertEquals('ADMIN' , $user->getAttribute('role'));
        $this->assertEquals('' , $user->getAttribute('birthdayDate'));
    }

    /**
     * @depends testVerifyPasswordWithSessionAttributes
     */
    function testVerifyPasswordWithAllSessionAttributes()
    {
        $config = array(
            'profile' => 'dbdaotest',
            'dao' => 'test~user',
            'sessionAttributes' => 'ALL'
        );
        $authDb = new dbdaoBackend($config);
        $user = $authDb->verifyAuthentication('john', 'passjohn');
        $this->assertIsObject($user);
        $this->assertEquals('John Doe' , $user->getName());
        $this->assertEquals('john@jelix.org' , $user->getEmail());
        $this->assertEquals('' , $user->getAttribute('role'));
        $this->assertEquals('' , $user->getAttribute('birthdayDate'));

        $user = $authDb->verifyAuthentication('jane', 'passjane');
        $this->assertIsObject($user);
        $this->assertEquals('Jane Doe' , $user->getName());
        $this->assertEquals('jane@jelix.org' , $user->getEmail());
        $this->assertEquals('ADMIN' , $user->getAttribute('role'));
        $this->assertEquals('1990-01-01' , $user->getAttribute('birthdayDate'));
    }


    /**
     * @depends testVerifyPasswordWithAllSessionAttributes
     */
    function testCreateUser()
    {

        $authDb = $this->getBackend();
        $this->assertTrue($authDb->createUser('robert', 'passrobert', 'robert@tests.jelix', 'Robert Dupont'));
        $this->assertTrue($authDb->userExists('robert'));
        $user = $authDb->verifyAuthentication('robert', 'passrobert');
        $this->assertIsObject($user);
        $this->assertEquals('Robert Dupont' , $user->getName());
        $this->assertEquals('robert@tests.jelix' , $user->getEmail());
    }

    /**
     * @depends testCreateUser
     */
    function testChangePassword() {
        $authDb = $this->getBackend();
        $this->assertTrue($authDb->changePassword('robert', 'passrobert2'));
        $this->assertTrue($authDb->userExists('robert'));
        $this->assertFalse($authDb->verifyAuthentication('robert', 'passrobert'));
        $user = $authDb->verifyAuthentication('robert', 'passrobert2');
        $this->assertIsObject($user);
        $this->assertEquals('Robert Dupont' , $user->getName());
        $this->assertEquals('robert@tests.jelix' , $user->getEmail());
    }

    /**
     * @depends testChangePassword
     */
    function testDeleteUser()
    {
        $authDb = $this->getBackend();
        $user = $authDb->deleteUser('robert');
        $this->assertIsObject($user);
        $this->assertTrue($authDb->deleteUser('robert'));
        $this->assertEquals('Robert Dupont' , $user->getName());
        $this->assertEquals('robert@tests.jelix' , $user->getEmail());
        $this->assertFalse($authDb->userExists('robert'));
    }


}