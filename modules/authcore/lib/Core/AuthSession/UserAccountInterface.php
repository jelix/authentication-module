<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2022 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

namespace Jelix\Authentication\Core\AuthSession;


interface UserAccountInterface
{
    public function getAccountId();


    public function getUserName();

    public function getEmail();
}