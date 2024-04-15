<?php

use Jelix\Authentication\LoginPass\PasswordResetException;
use PHPUnit\Framework\TestCase;
use Jelix\Authentication\LoginPass\PasswordReset;
use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\JelixModule\Command\MailerTest;

class PasswordResetTest extends TestCase
{
    public function testSendEmailNoUser()
    {
        $manager = new ManagerForTests();
        $passReset = new PasswordReset(false, $manager);

        $this->expectException(PasswordResetException::class);
        $this->expectExceptionCode(PasswordResetException::CODE_BAD_LOGIN_EMAIL);

        $manager->user = null;
        $passReset->sendEmail(null);
    }

    public function testSendEmailNoEmail()
    {
        $manager = new ManagerForTests();
        $passReset = new PasswordReset(false, $manager);

        $this->expectException(PasswordResetException::class);
        $this->expectExceptionCode(PasswordResetException::CODE_BAD_LOGIN_EMAIL);

        $manager->user = new AuthUser('test', array());
        $passReset->sendEmail($manager->user);
    }

    public function testSendEmailNoStatus()
    {
        $manager = new ManagerForTests();
        $passReset = new PasswordReset(false, $manager);

        $this->expectException(PasswordResetException::class);
        $this->expectExceptionCode(PasswordResetException::CODE_BAD_STATUS);

        $manager->user = new AuthUser('test', array('email' => 'nottest.test@test.com'));
        $passReset->sendEmail($manager->user);
    }

    public function testSendEmailNoPasswordChangeAllowed()
    {
        $manager = new ManagerForTests();
        $passReset = new PasswordReset(false, $manager);

        $this->expectException(PasswordResetException::class);
        $this->expectExceptionCode(PasswordResetException::CODE_BAD_STATUS);

        $manager->user = new AuthUser('test', array('email' => 'test.test@test.com'));
        $manager->canChangePassword = false;
        $passReset->sendEmail($manager->user);

    }

    public function testSendEmailBadUserStatus()
    {
        $manager = new ManagerForTests();
        $passReset = new PasswordReset(false, $manager);

        $this->expectException(PasswordResetException::class);
        $this->expectExceptionCode(PasswordResetException::CODE_BAD_STATUS);

        $manager->user = new AuthUser('test', array('email' => 'test.test@test.com', 'status' => AuthUser::STATUS_DEACTIVATED));
        $manager->canChangePassword = true;
        $passReset->sendEmail($manager->user);
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

    public function getBadCheckData()
    {
        return array(
            array(
                null, // user


            )
        );
    }

    public function testSendMail()
    {
        $manager = new ManagerForTests();
        $manager->user = null;
        $manager->_canChangePassword = true;

        $config = $this->createMock(\Jelix\Authentication\LoginPass\Config::class);
        $config->method('sendHtmlEmail')->willReturn(true);
        $config->method('isPasswordChangeEnabled')->willReturn(true);
        $config->method('isAccountChangeEnabled')->willReturn(true);
        $config->method('getPublicUserProperties')->willReturn(array('login'));
        $config->method('getDomainAndServerURI')->willReturn(array('example.com', 'https://example.com'));
        $config->method('getValidationKeyTTL')->willReturn(new \DateInterval('PT20M'));

        $passReset = new PasswordReset(false, $manager, $config);

        try {
            $passReset->sendEmail(null);
            $this->fail('no fail on missing user');
        }
        catch(PasswordResetException $e)
        {
            $this->assertEquals(PasswordResetException::CODE_BAD_LOGIN_EMAIL, $e->getCode());
        }

        try { // email is missing
            $manager->user = new AuthUser('test', array('login' => 'test'));
            $passReset->sendEmail($manager->user);
            $this->fail('no fail on missing email');
        }
        catch(PasswordResetException $e)
        {
            $this->assertEquals(PasswordResetException::CODE_BAD_LOGIN_EMAIL, $e->getCode());
        }

        $manager->user = new AuthUser('test', array('login' => 'test', 'email'=>'test@example.com'));

        try { // cannot change password
            $manager->_canChangePassword = false;
            $passReset->sendEmail($manager->user);
            $this->fail('no fail whereas password cannot change');
        }
        catch(PasswordResetException $e)
        {
            $this->assertEquals(PasswordResetException::CODE_BAD_STATUS, $e->getCode());
        }
        $manager->_canChangePassword = true;

        try { // status deactivated
            $manager->user->setAttribute('status', AuthUser::STATUS_DEACTIVATED);
            $passReset->sendEmail($manager->user);
            $this->fail('no fail whereas user status = deactivated');
        }
        catch(PasswordResetException $e)
        {
            $this->assertEquals(PasswordResetException::CODE_BAD_STATUS, $e->getCode());
        }
        $manager->user->setAttribute('status', AuthUser::STATUS_NEW);

        $requestId = $passReset->sendEmail($manager->user);
        $this->assertNotEmpty($requestId);
    }
}

class ManagerForTests
{
    public $user;

    public $_canChangePassword;

    public function getUser($login)
    {
        return $this->user;
    }

    public function canChangePassword()
    {
        return $this->_canChangePassword;
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