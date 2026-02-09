<?php

namespace Jelix\Authentication\AuthAdmin;

use jAcl2;
use Jelix\AdminUI\SideBar\SubMenu;
use jEventListener;
use jLocale;

class AdminUiEventListener extends jEventListener
{
    protected $eventMapping = array(
        'adminui.loading' => 'onAdminUILoading',
    );

    /**
     * @param jEvent $event
     */
    public function onAdminUILoading($event)
    {
        if(jAcl2::check('auth.idpadmin.view')) {
            /** @var \Jelix\AdminUI\UIManager $uim */
            $uim = $event->uiManager;

            $adminMenu = new SubMenu('admin', 'Authentification', 10);
            $adminMenu->addJelixLinkItem(jLocale::get('authadmin~default.navigation.menu.idp'), 'authadmin~idpadmin:index', array(), 'address-book');
            $uim->sidebar()->addMenuItem($adminMenu);
        }

    }
}
