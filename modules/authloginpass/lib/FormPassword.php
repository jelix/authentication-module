<?php
/**
 * @author       Laurent Jouanneau <laurent@jelix.org>
 * @copyright    2023-2024 Laurent Jouanneau
 *
 * @link         https://jelix.org
 * @licence      MIT
 */
namespace Jelix\Authentication\LoginPass;

use Jelix\Authentication\Core\Utils\Password;

class FormPassword
{

    static function getFormAndWidget($formName, $passwdCtrlName)
    {
        $form = \jForms::get($formName);
        if($form == null){
            $form = \jForms::create($formName);
        }
        $widget = self::getWidget($form, $passwdCtrlName);
        return  [$form, $widget];
    }

    static function getWidget($form, $passwdCtrlName)
    {
        $confirm = $form->getControl($passwdCtrlName.'_confirm');
        if ($confirm) {
            $confirm->deactivate(true);
        }
        return 'passwordeditor_html';
    }

    static function checkPassword($password)
    {
        return (Password::checkPasswordStrength($password) > Password::STRENGTH_WEAK);
    }
}