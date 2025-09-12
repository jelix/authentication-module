<?php

namespace TestAuth;

use jEventListener;

class AuthCoreEventListener extends jEventListener
{
    /**
     * @param jEvent $event
     */
    public function ondeclareIDPlugin($event)
    {
        $event->add(['pluginName' => 'alwadysyes']);
    }
}
