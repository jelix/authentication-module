<?php
/**
*
* @copyright 2024 Laurent Jouanneau and other contributors
* @license   MIT
*/
use Jelix\Authentication\Account\Manager;

class defaultCtrl extends jControllerDaoCrud {
    protected $dao = 'account~accounts';

    protected $form = 'accountadmin~account_admin';

    public function _afterUpdate($form, $id, $resp) {
        // fire event ?
        \jEvent::notify('AccountUserUpdate', array(
            'account_id' => $id
        ));
        return $resp;
    }

    public function _afterCreate($form, $id, $resp) {
        // fire event ?
        \jEvent::notify('AccountUserCreate', array(
            'account_id' => $id
        ));
        return $resp;
    }

    public function _delete($id, $resp) {
        // fire event ?
        \jEvent::notify('AccountUserDelete', array(
            'account_id' => $id
        ));
        return $resp;
    }

    public function _create($form, $resp, $tpl) {
        /**  @var $form \jFormsBase */
        $form->getControl('account_id')->deactivate();
        $form->getControl('create_date')->deactivate();
        $form->getControl('modify_date')->deactivate();
    }
}
