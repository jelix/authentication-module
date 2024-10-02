<?php
/**
* @copyright 2019-2024 Laurent Jouanneau and other contributors
* @license    MIT
*/

use Jelix\Authentication\Account\Manager;
use Jelix\Authentication\Account\Account;

class profileCtrl extends jController {

    public $pluginParams = array( '*' => array( 'auth.required' => true ));

    function index() {
        $rep = $this->getResponse('html');

        $currentUser = Manager::getCurrentUserAccount();
        $formId = $currentUser->getData('account_id');
        $form = jForms::get('account~profile_modify', $formId);
        if (!$form) {
            $form = jForms::create('account~profile_modify', $formId);
            if (!$form) {
                jMessage::add(jLocale::get('account.profile.modify.form.error'), 'error');

                return $rep;
            }
        }

        $form->initFromDao('account~accounts', $formId);

        $tpl = new \jTpl();
        $tpl->assign('form', $form);
        $content = $tpl->fetch('profile_index');
        $rep->body->assign('MAIN', $content);
    
        return $rep;
    }

    public function modify()
    {
        $rep = $this->getResponse('html');

        $currentAccount = Manager::getCurrentUserAccount();

        $formId = $currentAccount->getData('account_id');
        $form = jForms::get('account~profile_modify', $formId);
        if (!$form) {
            $form = jForms::create('account~profile_modify', $formId);
            if (!$form) {
                jMessage::add(jLocale::get('account.profile.modify.form.error'), 'error');

                return $rep;
            }
        }

        $form->initFromDao('account~accounts', $formId);

        $tpl = new jTpl();
        $tpl->assign('form', $form);
        $rep->body->assign('MAIN', $tpl->fetch('profile_modify'));
        return $rep;
    }

    public function save()
    {
        $rep = $this->getResponse('redirect');

        $account = Manager::getCurrentUserAccount();
        if (!$account) {
            $rep->action = 'account~profile:index';

            return $rep;
        }
        $accountId = $account->getAccountId();
        $form = jForms::get('account~profile_modify', $accountId);
        
        if (!$form) {
            jMessage::add(jLocale::get('account.profile.modify.form.error'), 'error');
            $rep->action = 'account~profile:index';

            return $rep;
        }

        $form->initFromRequest();

        if (!$form->check()) {
            $rep->action = 'account~profile:modify';

            return $rep;
        }

        $newInfos = array_filter($form->getAllData(), function ($key) use ($form) {
            if ($form->getControl($key)->isReadOnly()) {
                return false;
            }
            return true ;
        }, ARRAY_FILTER_USE_KEY);

        // update account information into the session
        $updatedAccount = Manager::modifyInfos($newInfos,$accountId);

        $user = jAuthentication::getCurrentUser();
        $user->setAccount($updatedAccount);

        jForms::destroy('account~profile_modify', $accountId);
        $rep->action = 'account~profile:index';

        return $rep;
    }
}

