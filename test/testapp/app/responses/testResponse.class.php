<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

require_once (JELIX_LIB_PATH.'core/response/jResponseHtml.class.php');

class testResponse extends jResponseHtml {


   public $bodyTpl = 'test~main';

   // modifications communes aux actions utilisant cette reponses
   protected function doAfterActions(){
       $this->title .= ($this->title !=''?' - ':'').' Test auth';

       $this->body->assignIfNone('MAIN','<p>Empty page</p>');
       $this->body->assignIfNone('menu','<p></p>');
       $this->body->appendZone('SIDEBAR','test~sidebar');
       $this->body->assignIfNone('page_title','Application Test for authentication');
   }
}
