<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\LoginPass;

abstract class BackendAbstract implements BackendPluginInterface
{
    protected $labelLocale = '';

    /**
     * @var string auth provider id, should be redefined in child class
     */
    protected $authBackendKey = '';
    protected $_params = array();

    protected $passwordHashAlgo = 1;
    protected $passwordHashOptions;

    public function __construct($params)
    {
        $this->_params = $params;
        if (isset($params['passwordHashAlgo'])) {
            $this->passwordHashAlgo = intval($params['passwordHashAlgo']);
            if ($this->passwordHashAlgo < 1) {
                $this->passwordHashAlgo = 1;
            }
        }
        $this->passwordHashOptions = $params['passwordHashOptions'];
        if (isset($params['label'])) {
            $this->label = $params['label'];
        }
    }

    public function getLabel()
    {
        if (isset($this->_params['backendLabel'])) {
            return $this->_params['backendLabel'];
        } elseif ($this->labelLocale) {
            return \jLocale::get($this->labelLocale);
        }
        return $this->authBackendKey;
    }

    public function getRegisterKey()
    {
        return $this->authBackendKey;
    }

    public function setRegisterKey($key)
    {
        $this->authBackendKey = $key;
    }

    public function getConfiguration()
    {
        return $this->_params;
    }

    /**
     * hash the given password with an old method used in deprecated jAuth system
     *
     * @param string $password the password to hash
     * @return string the hash password
     */
    protected function oldHashPassword($password)
    {
        if (isset($this->_params['deprecatedPasswordCryptFunction']) && $this->_params['deprecatedPasswordCryptFunction']) {
            $f = $this->_params['deprecatedPasswordCryptFunction'];
            if ($f != '') {
                if ($f[1] == ':') {
                    $t = $f[0];
                    $f = substr($f, 2);
                    if ($t == '1') {
                        return $f((isset($this->_params['deprecatedPasswordSalt'])?$this->_params['deprecatedPasswordSalt']:''), $password);
                    } elseif ($t == '2') {
                        return $f($this->_params, $password);
                    }
                }
                return $f($password);
            }
        }
        return $password;
    }

    /**
     * @param string $givenPassword     the password to verify
     * @param string $currentPasswordHash the hash of the real password
     * @return boolean|string false if password does not correspond. True if it is ok. A string
     * containing a new hash if it is ok and need to store a new hash
     */
    protected function checkPassword($givenPassword, $currentPasswordHash)
    {
        if ($currentPasswordHash[0] == '$') {
            // ok, we have hash for standard API, let's use standard API
            if (!password_verify($givenPassword, $currentPasswordHash)) {
                return false;
            }

            // check if rehash is needed,
            if (password_needs_rehash($currentPasswordHash, $this->passwordHashAlgo, $this->passwordHashOptions)) {
                return $this->hashPassword($givenPassword);
            }
        } else {
            // verify with the old hash api
            if (!hash_equals($currentPasswordHash, $this->oldHashPassword($givenPassword))) {
                return false;
            }

            // let's rehash the password with a standard method
            return $this->hashPassword($givenPassword);
        }
        return true;
    }

    protected function hashPassword($password) {
        return password_hash($password, $this->passwordHashAlgo, $this->passwordHashOptions);
    }
}
