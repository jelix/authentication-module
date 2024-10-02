<?php
/**
 * @author    Laurent Jouanneau <laurent@jelix.org>
 * @copyright 2018-2024 Laurent Jouanneau
 *
 * @link      https://jelix.org
 * @licence   MIT
 */

namespace Jelix\Authentication\LoginPass;

class PasswordResetException extends \Exception
{
    const CODE_BAD_LOGIN_EMAIL = 1;
    const CODE_BAD_STATUS = 2;
    const CODE_MAIL_SERVER_ERROR = 3;
    const CODE_BAD_CONFIRMATION_CODE = 4;

    const messages = array(
        1 => 'badloginemail',
        2 => 'badstatus',
        3 => 'smtperror',
        4 => 'badkey',
    );

    public function __construct($code = 0)
    {
        $messageCode = self::messages[$code] ?? 'Unknown error';

        $message =  \jLocale::get('authcore~auth.request.confirmation.error.'.$messageCode);

        parent::__construct($message, $code);
    }
}
