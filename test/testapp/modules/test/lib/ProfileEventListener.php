<?php

namespace TestAuth;

use Jelix\Authentication\Account\ProfileViewPageEvent;
use jEventListener;

class ProfileEventListener extends jEventListener
{
    public function onProfileViewPageEvent(ProfileViewPageEvent $event)
    {
        $tpl = $event->getTemplateService();

        $tpl->assign('position', 'above');
        $event->addContent($tpl->fetch('test~profilePageExtended'), 6);
        $tpl->assign('position', 'below');
        $event->addContent($tpl->fetch('test~profilePageExtended'), 3);
        $event->addContent('<br>content on same position use insertion order (1)', 8);
        $event->addContent('<br>content on same position use insertion order (2)', 8);
    }
}
