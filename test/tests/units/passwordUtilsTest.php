<?php

use PHPUnit\Framework\TestCase;
use Jelix\Authentication\Core\Utils\Password;

class PasswordUtilsTest extends TestCase
{

    public function testRandomPassword()
    {
        $pass = Password::getRandomPassword();
        $this->assertEquals(12, strlen($pass));

    }
}