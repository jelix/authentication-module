<?php

namespace Jelix\Authentication\Account;

use jEvent;
use jTpl;

class ProfileViewPageEvent extends jEvent
{
    private jTpl $tpl;

    private $contentList = [];

    public function __construct(jTpl $tpl)
    {
        $this->tpl = $tpl;
        parent::__construct('ProfileViewPageEvent');
    }

    public function getTemplateService(): jTpl
    {
        return $this->tpl;
    }

    public function addContent($content, $order)
    {
        if(!isset($this->contentList[$order])) {
            $this->contentList[$order] =  [];
        }
        $this->contentList[$order][] = $content;
    }

    public function buildContent()
    {
        ksort($this->contentList);
        $sortedContent = '';
        foreach($this->contentList as $content) {
            $sortedContent .= implode('', $content);
        }
        return $sortedContent;
    }

}
