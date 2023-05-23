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
account.enabled=on

[coordplugins]
sessionauth=on


[responses]
html="\Jelix\AdminUI\Responses\AdminUIResponse"
htmlerror="\Jelix\AdminUI\Responses\AdminUIResponse"
htmllogin="\Jelix\AdminUI\Responses\AdminUIBareResponse"

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

notFoundAct="manager~default:notfound"

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
adminlte-bootstrap.require=jquery,jquery_ui
adminlte-bootstrap.js[]=adminlte-assets/plugins/bootstrap/js/bootstrap.bundle.min.js

adminlte-fontawesome.css[]=adminlte-assets/plugins/fontawesome-free/css/all.min.css

adminlte.require=jquery,adminlte-bootstrap,adminlte-fontawesome
adminlte.css[]=adminlte-assets/dist/css/adminlte.min.css
adminlte.css[]=adminlte-assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css
adminlte.css[]=adminui-assets/SourceSansPro/SourceSansPro.css
adminlte.css[]=adminui-assets/adminui.css
adminlte.js[]=adminlte-assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js
adminlte.js[]=adminlte-assets/plugins/jquery-mousewheel/jquery.mousewheel.js
adminlte.js[]=adminlte-assets/plugins/fastclick/fastclick.js
adminlte.js[]=adminlte-assets/dist/js/adminlte.min.js
adminlte.js[]=adminui-assets/adminui.js

[adminui]
appVersion=0.0.1
htmlLogo="Jelix<b>Auth</b>"
htmlLogoMini="J<b>Auth</b>"
htmlCopyright="<strong>Copyright &copy; 2019-2022 Laurent Jouanneau</strong>."
dashboardTemplate=

appTitle=Auth test app
bodyCSSClass="hold-transition "
bareBodyCSSClass="hold-transition login-page"
adminlteAssetsUrl="adminlte-assets/"

; hide the dashboard item into the sidebar
disableDashboardMenuItem=off

; show the button into the header, to activate the full screen mode
fullScreenModeEnabled=off

; activate the dark mode
darkmode=off

; the header/navbar is fixed
header.fixed=off

; the header/navbar has a border
header.border=on

; Text of the header/navbar is small
header.smalltext=off

; Color of the header/navbar. see https://adminlte.io/docs/3.2/layout.html
header.color=cyan

; the text of the navbar is dark
header.darktext=on

; the text of the logo is small
header.brand.smalltext = off

; the sidebar is collapsed by default
sidebar.collapsed=off

; the sidebar is fixed
sidebar.fixed=off

; when collapsed, the sidebar is still visible in a mini format
sidebar.mini=on

; the sidebar has a flat style
sidebar.nav.flat.style=off

; the sidebar items are compact
sidebar.nav.compact=off

; child items into the sidebar, are indented
sidebar.nav.child.indent=off

;
sidebar.nav.child.collapsed=

; the text of the sidebar is small
sidebar.nav.smalltext = off

; the background of the sidebar is dark
sidebar.dark=on

; the selected item of the sidebar has the "primary" color. see https://adminlte.io/docs/3.2/layout.html
sidebar.current-item.color=cyan

; the footer is fixed
footer.fixed=off

; text of the footer is small
footer.smalltext = off

; the general text is small
body.smalltext = off

[authentication]
idp[]=loginpass
idp[]=alwaysyes
sessionHandler=php

[sessionauth]
authRequired=off
missingAuthAction="authcore~sign:in"
missingAuthAjaxAction=""

[loginpass_idp]
backends[]=ldap
backends[]=daotablesqlite
backends[]=inifile
after_login="adminui~default:index"

[loginpass:common]
passwordHashAlgo=1
passwordHashOptions=
deprecatedPasswordCryptFunction=
deprecatedPasswordSalt=

;ini file provider
[loginpass:inifile]
backendType=inifile
inifile="var:db/users.ini.php"
backendLabel="Native users"

[loginpass:daotablesqlite]
backendType=dbdao
profile=daotablesqlite

[loginpass:ldap]
backendType=ldap
profile=openldap
featureCreateUser=on
featureDeleteUser=on
featureChangePassword=on