;<?php die(''); ?>
;for security reasons , don't remove or modify the first line


[jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"
[acl2]
hiddenRights=
hideRights=off
driver=db
authAdapterClass="\Jelix\Authentication\Core\Acl2Adapter"
