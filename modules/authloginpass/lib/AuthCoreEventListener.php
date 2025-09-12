<?php

namespace jelix\Authentication\LoginPass;

use jEventListener;

class AuthCoreEventListener extends jEventListener
{
    /**
     * @param jEvent $event
     */
    public function ondeclareIDPlugin($event)
    {
        $event->add(['pluginName' => 'loginpass']);
    }
}
