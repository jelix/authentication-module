;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

locale=fr_FR
charset=UTF-8

availableLocales=fr_FR

; see http://www.php.net/manual/en/timezones.php for supported values
timeZone="Europe/Paris"

theme=adminlte

[modules]
jelix.enabled=on
jelix.installparam[wwwfiles]=vhost

test.enabled=on

jacl2.enabled=off
jacl2.installparam[eps]="[index,admin]"

jacl2db.enabled=off
jacl2db.installparam[defaultuser]=on
jacl2db.installparam[defaultgroups]=on

adminui.enabled=on
adminui.installparam[wwwfiles]=vhost

authcore.enabled=on
authloginpass.enabled=on

[coordplugins]
sessionauth = on


[responses]
html="module:adminui~adminuiResponse"
htmlerror="module:adminui~adminuiResponse"
htmllogin="module:adminui~adminuiBareResponse"

[error_handling]
messageLogFormat="%date%\t%ip%\t[%code%]\t%msg%\n\tat: %file%\t%line%\n\turl: %url%\n\t%http_method%: %params%\n\treferer: %referer%\n%trace%\n\n"
errorMessage="Une erreur technique est survenue. Désolé pour ce désagrément."


[compilation]
checkCacheFiletime=on
force=off

[jResponseHtml]
plugins=debugbar

[urlengine]

; enable the parsing of the url. Set it to off if the url is already parsed by another program
; (like mod_rewrite in apache), if the rewrite of the url corresponds to a simple url, and if
; you use the significant engine. If you use the simple url engine, you can set to off.
enableParser=on

multiview=off

; basePath corresponds to the path to the base directory of your application.
; so if the url to access to your application is http://foo.com/aaa/bbb/www/index.php, you should
; set basePath = "/aaa/bbb/www/".
; if it is http://foo.com/index.php, set basePath="/"
; Jelix can guess the basePath, so you can keep basePath empty. But in the case where there are some
; entry points which are not in the same directory (ex: you have two entry point : http://foo.com/aaa/index.php
; and http://foo.com/aaa/bbb/other.php ), you MUST set the basePath (ex here, the higher entry point is index.php so
; : basePath="/aaa/" )
basePath=

notfoundAct="jelix~error:notfound"

jelixWWWPath="jelix/"


[logger]
auth=file

[fileLogger]
default=messages.log
auth=auth.log

[mailer]
webmasterEmail="laurent@jelix.org"
webmasterName=Root
mailerType=file

[mailLogger]
email="root@localhost"

[webassets_common]

adminlte-bootstrap.require=jquery
adminlte-bootstrap.css[]="adminlte-assets/bower_components/bootstrap/dist/css/bootstrap.min.css"
adminlte-bootstrap.js[]="adminlte-assets/bower_components/bootstrap/dist/js/bootstrap.min.js"

adminlte-fontawesome.css[]="adminlte-assets/bower_components/font-awesome/css/font-awesome.min.css"

adminlte.require="jquery,adminlte-bootstrap,adminlte-fontawesome"
adminlte.css[]="adminlte-assets/bower_components/Ionicons/css/ionicons.min.css"
adminlte.css[]="adminlte-assets/dist/css/AdminLTE.min.css"
adminlte.css[]="adminlte-assets/dist/css/skins/_all-skins.min.css"
adminlte.css[]="adminlte-assets/SourceSansPro/SourceSansPro.css"
adminlte.css[]="adminlte-assets/adminui.css"
adminlte.js[]="adminlte-assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"
adminlte.js[]="adminlte-assets/bower_components/fastclick/lib/fastclick.js"
adminlte.js[]="adminlte-assets/dist/js/adminlte.min.js"
adminlte.js[]="adminlte-assets/adminui.js"

[adminui]
appVersion=0.0.1
htmlLogo="Jelix<b>Auth</b>"
htmlLogoMini="J<b>Auth</b>"
htmlCopyright="<strong>Copyright &copy; 2019 Laurent Jouanneau</strong>."
dashboardTemplate=

[authentication]
idp[]=loginpass
idp[]=alwaysyes
sessionHandler = php

[sessionauth]
authRequired = off
missingAuthAction = ""
missingAuthAjaxAction = ""

[loginpass_idp]
backends[]=ldap
backends[]=daotablesqlite
backends[]=inifile
after_login=

[loginpass:common]
passwordHashAlgo=1
passwordHashOptions=
deprecatedPasswordCryptFunction=
deprecatedPasswordSalt=

;ini file provider
[loginpass:inifile]
backendType=inifile
inifile="var:db/users.ini.php"
backendLabel=Native users

[loginpass:daotablesqlite]
backendType=dbdao
profile=daotablesqlite

[loginpass:ldap]
backendType=ldap
profile=openldap
featureCreateUser=on
featureDeleteUser=on
featureChangePassword=on


