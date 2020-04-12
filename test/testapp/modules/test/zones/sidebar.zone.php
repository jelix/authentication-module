<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

class sidebarZone extends jZone {
    protected $_tplname='sidebar';

    
    protected function _prepareTpl(){
        $this->_tpl->assign('LOGIN', '');
    }
}

