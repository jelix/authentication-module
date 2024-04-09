<?php
/**
 * @author     Laurent Jouanneau
 *
 * @copyright  2011-2024 Laurent Jouanneau
 *
 */

namespace Jelix\Authentication\Core\Utils;

class Password
{

    const KEYSPACE_BASIC = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const KEYSPACE_EXTENDED = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!#$%()*+-/.:<=>?@[]_,{}~';


    /**
     * generate a password with random letters, numbers and special characters.
     *
     * @param int $length the length of the generated password
     * @param string $keyspace list of allowed characters²
     *
     * @return string the generated password
     */
    public static function getRandomPassword($length = 12, $keyspace = '')
    {
        if ($length < 12) {
            $length = 12;
        }

        if ($keyspace == '') {
            $keyspace = self::KEYSPACE_BASIC;
        }

        $pass = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pass .= $keyspace[random_int(0, $max)];
        }
        return $pass;
    }

    const STRENGTH_NONE = 0;
    const STRENGTH_POOR = 1;
    const STRENGTH_BAD_PASS = -1;
    const STRENGTH_WEAK = 2;
    const STRENGTH_GOOD = 3;
    const STRENGTH_STRONG = 4;

    public static function checkPasswordStrength($password, $minLength = 12)
    {
        $score = 0;

        if ($password == '') {
            return self::STRENGTH_NONE;
        }

        $len = mb_strlen($password);
        if ($minLength > 0 && $len < $minLength) {
            return self::STRENGTH_POOR;
        }

        $poolSize = 0;
        $poolSize += preg_match("/[A-Z]/", $password) ? 26 : 0;
        $poolSize += preg_match("/[a-z]/", $password) ? 26 : 0;
        $poolSize += preg_match("/[0-9]/", $password) ? 10 : 0;
        $poolSize += preg_match("/_/", $password) ? 1 : 0;
        $poolSize += preg_match("/ /", $password) ? 1 : 0;
        $poolSize += preg_match("/@/", $password) ? 1 : 0;
        $poolSize += preg_match("/[éèêÈÉÊçÇàÀßùÙ]/", $password) ? 13 : 0;
        $poolSize += preg_match("/[îûôâëäöïüÿðÂÛÎÔÖÏÜËÄŸ]/", $password) ? 21 : 0;
        $poolSize += preg_match("/[æœÆŒ]/", $password) ? 4 : 0;
        $poolSize += preg_match("/[\-−‑–—]/", $password) ? 5 : 0;
        $poolSize += preg_match("/[\"'()!:;,?«»¿¡‚„“”…]/", $password) ? 18 : 0;
        $poolSize += preg_match("/[+*\/×÷≠]/", $password) ? 6 : 0;
        $poolSize += preg_match("/[&\$£%µ€#¢]/", $password) ? 7 : 0;
        $poolSize += preg_match("/[²Ø~©®™]/", $password) ? 6 : 0;
        $poolSize += preg_match("/[¬ ÞĿÐ¥þ↓←↑→⋅∕]/", $password) ? 13 : 0;
        $poolSize += preg_match("/[\[\]{}|]/", $password) ? 5 : 0;

        $entropy = $len * log($poolSize, 2);

        if ($entropy < 25) {
            return self::STRENGTH_POOR;
        }

        if ($entropy < 50) {
            return self::STRENGTH_WEAK;
        }
        $jAuthMostUsedPasswords = include(__DIR__ . '/MostUsedPasswords.php');

        foreach ($jAuthMostUsedPasswords as $badpassword) {
            //echo $badpassword."\n";
            if (preg_match("/(^|\\s)" . $badpassword . "($|\\s)/", $password)) {
                return self::STRENGTH_BAD_PASS;
            }
        }

        if ($entropy < 100) {
            return self::STRENGTH_GOOD;
        }
        return self::STRENGTH_STRONG;
    }
}