<?php


class accountListener extends jEventListener
{
    public function onCanAccountBeDeleted(jEvent $event) {
        $event->add(['allowDelete' => false]);
    }
}
