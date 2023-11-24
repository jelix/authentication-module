
Configuration of the authcore module
=====================================


Into the `app/system/mainconfig.ini.php` configuration file, you should find these values:

```ini
[coordplugins]
; activation of plugin that will check authentication on each page
sessionauth=on

[authentication]
; list of names of activated identity providers
idp[]=

; name of the session handler used to store session data for authentication
sessionHandler=php

[sessionauth]
; action to redirect to when authentication is needed and the user is not authenticated
missingAuthAction=
; action to redirect to when authentication is needed and the user is not 
; authenticated, during an ajax request
missingAuthAjaxAction=

; if true, all pages of the application need to be authenticated, except if
; the controller has a coord parameter `auth.required` to false. And vice-versa.
authRequired=off

```


Authentication into your application can rely on several identity providers (aka "idp").
An identity provider is a component that handle a type of authentication, or 
an authentication protocol. Its role is to verify that the user is authenticated,
or to check credentials when a user log in etc. 
 
Example of identify providers:
 
- authentication with a login and a password (against a database table, an ldap server etc..)
- authentication with the SAML protocol
- authentication with the CAS protocol
- authentication with the oAuth protocol
- http authentication
- etc.

Identity providers are implemented into a plugin of type `authidp`. Names you
give into the `idp` option are the name of plugins to activate. `idp` is an array.

The package provides a "login/password" identity provider, with the module
`authloginpass`. Name of the plugin is `loginpass`. 

JelixAuthentication keeps information about the authenticated user, into
the session handler. Two session handlers are provided:

- `php`: it stores authentication information into the PHP session
- `var`: it stores authentication information into an object. This is useful
  for stateless authentication. For exemple, to implement a webAPI where
  credentials should be given at each http request. Or to use authentication
  in a script running in a shell.

Session handlers are implemented as plugins of type `authsession`.


