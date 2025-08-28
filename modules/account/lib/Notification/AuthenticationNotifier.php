<?php

namespace Jelix\Authentication\Account\Notification;

use DateTimeImmutable;
use DomainException;
use Jelix\Authentication\Account\Account;
use Jelix\Core\Infos\AppInfos;

class AuthenticationNotifier
{
    public const NOTIFY_NEVER = 'never';
    public const NOTIFY_ALWAYS = 'always';
    public const NOTIFY_ON_USER_CAN_OPT_OUT = 'enabled';
    public const NOTIFY_OFF_USER_CAN_OPT_IN = 'disabled';

    private $notifyMode;
    public function __construct()
    {
        $config = \jApp::config()->authentication;
        if (isset($config['notifyAuthMode']) && $config['notifyAuthMode']) {
            $this->notifyMode = $config['notifyAuthMode'];
            if(!in_array($this->notifyMode, [self::NOTIFY_ALWAYS, self::NOTIFY_NEVER, self::NOTIFY_OFF_USER_CAN_OPT_IN, self::NOTIFY_ON_USER_CAN_OPT_OUT])) {
                throw new DomainException('not a valid notifyAuthMode value');
            }
        } else {
            // use never
            $this->notifyMode = self::NOTIFY_NEVER;
            trigger_error('no value defined for notifyAuthMode, using "never"', E_USER_NOTICE);
        }
    }

    private function isNotificationEnabled(Account $account)
    {
        if ($this->notifyMode == self::NOTIFY_ALWAYS || $this->notifyMode == self::NOTIFY_ALWAYS) {
            return $this->notifyMode == self::NOTIFY_ALWAYS;
        }
        // must check Account value
        $value = $account->getNotifyAuthSuccess();
        if($this->notifyMode == self::NOTIFY_OFF_USER_CAN_OPT_IN) {
            return $value == 1 ;
        }
        if($this->notifyMode == self::NOTIFY_ON_USER_CAN_OPT_OUT) {
            return $value != 0 ;
        }
    }

    public function successAuth(Account $account)
    {
        // notify
        if($this->isNotificationEnabled($account)) {
            $appInfos  = AppInfos::load();
            $appName = $appInfos->getLabel();
            $email = $account->getEmail();
            $mailer = new \jMailer();
            $mailer->addAddress($email);
            $mailer->Subject = \jLocale::get('account~account.email.auth.success.subject', [$email, $appName]);
            $tpl = $mailer->Tpl('account~mailBodyAuthSuccess', true);
            $tpl->assign('email', $email);
            $tpl->assign('appName', $appName);
            $tpl->assign('authDateTime', (new DateTimeImmutable())->format('Y-m-d H:i:s'));
            $mailer->send();
        }
    }

    public function canUsersOverwriteNotifConf(): bool
    {
        return in_array($this->notifyMode, [self::NOTIFY_OFF_USER_CAN_OPT_IN, self::NOTIFY_ON_USER_CAN_OPT_OUT]);
    }
}
