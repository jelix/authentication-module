<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2024 Laurent Jouanneau
 * @link      https://jelix.org
 * @licence   MIT
 */
namespace Jelix\Authentication\RequestConfirmation;

class RequestException extends \Exception
{
    const CODE_BAD_CONFIRMATION_CODE = 1;
    const CODE_EXPIRED_CODE = 2;

    const messages = array(
        1 => 'badkey',
        2 => 'expiredkey',
    );
    public function __construct($code = 0)
    {
        $messageCode = self::messages[$code] ?? 'Unknown error';

        $message =  \jLocale::get('authcore~auth.request.confirmation.error.'.$messageCode);

        parent::__construct($message, $code);
    }
}
