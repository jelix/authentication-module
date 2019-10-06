<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2019 Laurent Jouanneau
 * @link     http://jelix.org
 * @licence MIT
 */

/**
 * a special if block to test easily if the current user is not connected
 *
 * <pre>{ifusernotauthenticated} ..here generated content if the user is NOT authenticated {/ifusernotauthenticated}</pre>
 *
 * @param jTplCompiler $compiler the template compiler
 * @param boolean $begin true if it is the begin of block, else false
 * @param array $params no parameters. array should be empty
 * @return string the php code corresponding to the begin or end of the block
 */
function jtpl_block_common_ifusernotauthenticated($compiler, $begin, $params=array())
{
    if($begin){
        if(count($params)){
            $content='';
            $compiler->doError1('errors.tplplugin.block.too.many.arguments','ifuserauthenticated');
        }else{
            $content = ' if(!jAuthentication::isCurrentUserAuthenticated()):';
        }
    }else{
        $content = ' endif; ';
    }
    return $content;
}
