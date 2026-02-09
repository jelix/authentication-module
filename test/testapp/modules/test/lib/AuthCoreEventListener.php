<?php

namespace TestAuth;

use jEventListener;

class AuthCoreEventListener extends jEventListener
{
    /**
     * @param jEvent $event
     */
    public function onAuthAdminGetIDPPlugin($event)
    {
        $event->add(['pluginName' => 'alwaysyes']);
    }
}
