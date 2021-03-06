<?php
/**
* @author       Laurent Jouanneau <laurent@jelix.org>
* @copyright    2015 Laurent Jouanneau
*
* @link         http://jelix.org
* @licence      http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

namespace Jelix\Authentication\LoginPass;

class Config
{
    protected $responseType = 'html';

    protected $registrationEnabled = true;

    protected $resetPasswordEnabled = true;

    protected $resetAdminPasswordEnabled = true;

    protected $passwordChangeEnabled = true;

    protected $accountDestroyEnabled = true;

    protected $verifyNickname = true;

    protected $publicProperties = array('login', 'nickname', 'create_date');

    protected $config;

    /**
     * @var integer  TTL in minutes
     */
    protected $validationKeyTTL = 1440; // 24h

    /**
     * Indicate if jcommunity should take care of this following rights:
     * - auth.user.modify
     * - auth.user.change.password
     * @var bool
     */
    protected $useJAuthDbAdminRights = false;

    /**
     */
    public function __construct($appConfig = null)
    {
        if ($appConfig) {
            $this->config = $appConfig;
        } else {
            $this->config = \jApp::config();
        }
        $config = (isset($this->config->loginpass_idp) ? $this->config->loginpass_idp : array());

        foreach(array(
            'responseType' => 'loginResponse',
            'verifyNickname' => 'verifyNickname',
            'passwordChangeEnabled' => 'passwordChangeEnabled',
            'accountDestroyEnabled' => 'accountDestroyEnabled',
            'useJAuthDbAdminRights' => 'useJAuthDbAdminRights',
            'validationKeyTTL' => 'validationKeyTTL',
                ) as $prop => $param) {
            if (array_key_exists($param, $config)) {
                $this->$prop = $config[$param];
            }
        }


        if (array_key_exists('publicProperties', $config)) {
            if (!is_array($config['publicProperties'])) {
                $this->publicProperties = preg_split('/\s*,\s*/', trim($config['publicProperties']));
            }
            else {
                $this->publicProperties = $config['publicProperties'];
            }
        }
        if (array_key_exists('registrationEnabled', $config)) {
            $this->registrationEnabled = (bool) $config['registrationEnabled'];
        }
        if (array_key_exists('resetPasswordEnabled', $config)) {
            $this->resetPasswordEnabled = (bool) $config['resetPasswordEnabled'];
        }
        if (array_key_exists('resetAdminPasswordEnabled', $config)) {
            $this->resetAdminPasswordEnabled = (bool) $config['resetAdminPasswordEnabled'];
        }
        $sender = filter_var($this->config->mailer['webmasterEmail'], FILTER_VALIDATE_EMAIL);
        if (!$sender) {
            // if the sender email is not configured, deactivate features that
            // need to send an email
            $this->resetPasswordEnabled = false;
            $this->resetAdminPasswordEnabled = false;
            $this->registrationEnabled = false;
        }
    }

    public function getResponseType()
    {
        return $this->responseType;
    }

    public function isRegistrationEnabled()
    {
        return $this->registrationEnabled;
    }

    public function isResetPasswordEnabled()
    {
        return $this->resetPasswordEnabled;
    }

    public function isResetAdminPasswordEnabled()
    {
        return $this->resetAdminPasswordEnabled;
    }

    public function isResetAdminPasswordEnabledForAdmin()
    {
        if ($this->useJAuthDbAdminRights) {
            return $this->resetAdminPasswordEnabled &&
                \jAcl2::check('auth.users.change.password');
        }
        return $this->resetAdminPasswordEnabled;
    }

    public function isPasswordChangeEnabled()
    {
        if ($this->useJAuthDbAdminRights) {
            return $this->passwordChangeEnabled &&
                \jAcl2::check('auth.user.change.password');
        }
        return $this->passwordChangeEnabled;
    }

    public function isAccountChangeEnabled() {
        if ($this->useJAuthDbAdminRights) {
            return \jAcl2::check('auth.user.modify');
        }
        return true;
    }

    public function isAccountDestroyEnabled() {
        return $this->accountDestroyEnabled && $this->isAccountChangeEnabled();
    }

    public function verifyNickname()
    {
        return $this->verifyNickname;
    }

    /**
     * @return \DateInterval
     * @throws \Exception
     */
    public function getValidationKeyTTL()
    {
        $ttl = intval($this->validationKeyTTL);
        if ($ttl < 5) {
            $ttl = 5;
        }
        else if ($ttl > 10080) {
            $ttl = 10080;
        }
        $dt = new \DateInterval('PT'.$ttl.'M');
        return $dt;
    }

    public function getValidationKeyTTLAsString()
    {
        $dt = $this->getValidationKeyTTL();
        $from = new \DateTime();
        $to = clone $from;
        $to = $to->add($dt);
        $ttl = $from->diff($to);

        $str = '';
        if ($ttl->d > 0) {
            $str .= $ttl->d . ' '.\jLocale::get('authloginpass~auth.account.duration.day'.($ttl->d > 1?'s':''));
        }
        if ($ttl->h > 0) {
            $str .= ' ' . $ttl->h . ' '.\jLocale::get('authloginpass~auth.account.duration.hour'.($ttl->h > 1?'s':''));
        }
        if ($ttl->i > 0) {
            $str .= ' ' . $ttl->i . ' '.\jLocale::get('authloginpass~auth.account.duration.minute'.($ttl->i > 1?'s':''));
        }

        return trim($str);
    }

    public function getPublicUserProperties()
    {
        if ($this->useJAuthDbAdminRights && ! \jAcl2::check('authloginpass~auth.user.view')) {
            return array('login');
        }
        return $this->publicProperties;
    }
}
