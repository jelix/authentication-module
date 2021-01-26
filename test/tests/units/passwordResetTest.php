<?php

use PHPUnit\Framework\TestCase;
use Jelix\Authentication\LoginPass\PasswordReset;
use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\JelixModule\Command\MailerTest;

class PasswordResetTest extends TestCase
{
    public function testSendEmailErrorLoginEmailStatus()
    {
        $userTab = array(
            null,
            new AuthUser('test', array()),
            new AuthUser('test', array('email' => 'nottest.test@test.com')
        ));
        $manager = new ManagerForTests();
        $passReset = new PasswordReset(false, false, $manager);
        foreach ($userTab as $user) {
            $manager->user = $user;
            $this->assertEquals(PasswordReset::RESET_BAD_LOGIN_EMAIL, $passReset->sendEmail('test', 'test.test@test.com'));
        }
        $manager->user = new AuthUser('test', array('email' => 'test.test@test.com'));
        $manager->canChangePassword = false;
        $this->assertEquals(PasswordReset::RESET_BAD_STATUS, $passReset->sendEmail('test', 'test.test@test.com'));
        $manager->user = new AuthUser('test', array('email' => 'test.test@test.com', 'status' => AuthUser::STATUS_DEACTIVATED));
        $manager->canChangePassword = true;
        $this->assertEquals(PasswordReset::RESET_BAD_STATUS, $passReset->sendEmail('test', 'test.test@test.com'));
    }

    /**
     * @FIXME Find a way to get a Tpl without config (there is a dependency to jApp::config() in jTpl::__construct()
     * that makes impossible to instanciate a tpl in test Context)
     */
    // public function testSendEmail()
    // {
    //     $manager = new ManagerForTests();
    //     $passReset = $this->getMockBuilder(PasswordReset::class)
    //         ->setMethods(array('getWebInfos', 'getMail'))
    //         ->setConstructorArgs(array(false, false, $manager))
    //         ->getMock();
    //     $passReset->method('getWebInfos')->willReturn(array('', ''));
    //     $passReset->method('getMail')->willReturn(new jMailerForTests());
    //     // $testapp = realpath(__DIR__.'/../../testapp').'/';
    //     // echo $testapp.PHP_EOL;
    //     // jApp::initPaths($testapp, null, $testapp.'var/', $testapp.'var/log/', $testapp.'config/', null);
    //     // jApp::loadConfig('mainconfig.ini.php');

    //     // jApp::setConfig((object)parse_ini_file($testapp.'/app/system/mainconfig.ini.php'));
        
    //     $manager->canChangePassword = true;
    //     $manager->user = new AuthUser('test', array('email' => 'test.test@test.com', 'status' => AuthUser::STATUS_VALID));
    //     $this->assertEquals(PasswordReset::RESET_OK, $passReset->sendEmail('test', 'test.test@test.com'));
    // }

        public function testCheckKey()
        {
            $manager = new ManagerForTests();
            $manager->user = null;
            $manager->canChangePassword = true;
            $config = (object)array();
            $config->mailer = array('webmasterEmail' => 'test.test@test.com');
            $passReset = new PasswordReset(false, false, $manager, $config);
            $this->assertEquals(PasswordReset::RESET_BAD_KEY, $passReset->checkKey('', ''));
            $this->assertEquals(PasswordReset::RESET_BAD_KEY, $passReset->checkKey('test', ''));
            $this->assertEquals(PasswordReset::RESET_BAD_KEY, $passReset->checkKey('', 'test'));
            $this->assertEquals(PasswordReset::RESET_BAD_KEY, $passReset->checkKey('test', 'test'));
            $manager->user = new AuthUser('test', array('login' => 'test', 'keyactivate' => '', 'request_date' => ''));
            $this->assertEquals(PasswordReset::RESET_BAD_KEY, $passReset->checkKey('test', 'test'));
            $manager->user = new AuthUser('test', array('login' => 'test', 'keyactivate' => 'abcdefg', 'request_date' => 'date'));
            $manager->user->setAttribute('status', AuthUser::STATUS_DEACTIVATED);
            $this->assertEquals(PasswordReset::RESET_BAD_STATUS, $passReset->checkKey('test', 'abcdefg'));
            $manager->user->setAttribute('status', AuthUser::STATUS_VALID);
            $this->assertEquals(PasswordReset::RESET_ALREADY_DONE, $passReset->checkKey('test', 'abcdefg'));
            $date = DateTime::createFromFormat('U', '12');
            $manager->user->setAttribute('request_date', $date->format('Y-m-d H:i:s'));
            $manager->user->setAttribute('status', AuthUser::STATUS_PWD_CHANGED);
            $this->assertEquals(PasswordReset::RESET_EXPIRED_KEY, $passReset->checkKey('test', 'abcdefg'));
            $date = new DateTime();
            $manager->user->setAttribute('request_date', $date->format('Y-m-d H:i:s'));
            $this->assertEquals($manager->user, $passReset->checkKey('test', 'abcdefg'));
        }
}

class ManagerForTests
{
    public $user;

    public $canChangePassword;

    public function getUser($login)
    {
        return $this->user;
    }

    public function canChangePassword()
    {
        return $this->canChangePassword;
    }
}

class jMailerForTests
{
    public $body;

    public function msgHTML($body, $baseDir, $advanced)
    {
        $this->body = $body;
    }

    public function Send()
    {
    }
}