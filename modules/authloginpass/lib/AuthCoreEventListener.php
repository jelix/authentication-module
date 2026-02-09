<?php

namespace jelix\Authentication\LoginPass;

use jEventListener;

class AuthCoreEventListener extends jEventListener
{
    /**
     * @param jEvent $event
     */
    public function onAuthAdminGetIDPPlugin($event)
    {
        $event->add(['pluginName' => 'loginpass']);
    }
}
