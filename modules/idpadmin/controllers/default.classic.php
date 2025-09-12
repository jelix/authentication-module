<?php

use Jelix\Authentication\IdpAdmin\IdpFinder;
use Jelix\IniFile\IniModifier;

class defaultCtrl extends jController
{
    public $pluginParams = [
        '*' => array('auth.required' => false, 'jacl2.right' => 'idpadmin.view'),
        'save' => array('jacl2.right' => 'idpadmin.edit'),
    ];

    private $idpList ;

    public function __construct(jRequest $req)
    {
        $idpFinder = new IdpFinder();
        $this->idpList = $idpFinder->findAllIDP();
        parent::__construct($req);
    }

    public function index()
    {
        $resp = $this->getResponse('html');
        // build form
        $form = jForms::create('idpadmin~idp');
        $this->buildForm($form);
        foreach($this->idpList as $idpInfo) {
            $form->setData('chck_'.$idpInfo[0], $idpInfo[1]);
        }
        $tpl = new jTpl();
        $tpl->assign('idps', $this->idpList);
        $tpl->assign('form', $form);
        $resp->body->assign('MAIN', $tpl->fetch('idp.list'));

        return $resp;
    }

    public function prepareEdit()
    {
        $form = jForms::create('idpadmin~idp');
        $this->buildForm($form);
        foreach($this->idpList as $idpInfo) {
            $form->setData('chck_'.$idpInfo[0], $idpInfo[1]);
        }
        return $this->redirect('idpadmin~default:showEdit');
    }

    public function showEdit()
    {
        $form = jForms::get('idpadmin~idp');
        if(is_null($form)) {
            return $this->redirect('idpadmin~default:prepareEdit');
        }
        $this->buildForm($form);
        $resp = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('idps', $this->idpList);
        $tpl->assign('form', $form);
        $resp->body->assign('MAIN', $tpl->fetch('idp.edit'));

        return $resp;
    }

    public function save()
    {
        $modif = new IniModifier(jApp::appPath('app/system/mainconfig.ini.php'));

        // build form
        $form = jForms::get('idpadmin~idp');
        $this->buildForm($form);
        $form->initFromRequest();

        if(!$form->check()) {
            return $this->redirect('idpadmin~default:showEdit');
        }

        $enabledIdp = [];
        foreach($this->idpList as $idp) {
            $name = $idp[0];
            if ($form->getData('chck_'.$name) == 1) {
                $enabledIdp[] = $name;
            }
        }
        $sessionIdp = jAuthentication::session()->getIdentityProviderId();
        if (!in_array($sessionIdp, $enabledIdp)) {
            $form->setErrorOn('chck_'.$name, jLocale::get('default.form.error.session.idp.disabling.forbidden'));
            return $this->redirect('idpadmin~default:showEdit');
        }
        $modif->setValues(['idp' => $enabledIdp], 'authentication');
        $modif->save();
        jForms::destroy('idpadmin~idp');

        return $this->redirect('idpadmin~default:index');
    }

    protected function buildForm(jFormsBase $form)
    {
        foreach($this->idpList as $idpInfo) {
            $name = $idpInfo[0];
            $ctrlStatus = new jFormsControlCheckbox('chck_'.$name);
            $ctrlStatus->label = $name;
            $ctrlStatus->valueLabelOnCheck = jLocale::get('jelix~ui.buttons.enabled');
            $ctrlStatus->valueLabelOnUncheck = jLocale::get('jelix~ui.buttons.disabled');
            $form->addControl($ctrlStatus);
        }
    }
}
