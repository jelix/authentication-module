<?php

namespace Jelix\Authentication\IdpAdmin;

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
        if(jAcl2::check('idpadmin.view')) {
            /** @var \Jelix\AdminUI\UIManager $uim */
            $uim = $event->uiManager;

            $adminMenu = new SubMenu('admin', 'Administration', 10);
            $adminMenu->addJelixLinkItem(jLocale::get('idpadmin~default.navigation.menu.idp'), 'idpadmin~default:index', array(), 'address-book');
            $uim->sidebar()->addMenuItem($adminMenu);
        }

    }
}
