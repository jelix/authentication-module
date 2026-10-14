Jelix Authentication
====================

It is an authentication framework for Jelix Applications.
It provides a set of features and API to implement authentication that match your needs.
It brings also an account management. However, it doesn't manage authorizations or rights.

stable version
--------------

See the version 0.4 ([0.4.x branch](https://github.com/jelix/authentication-module/tree/0.4.x)) 
to have a first stable version having only a few features. Even if it is usable, this is not really recommended for production.

Jelix 1.8.0 and higher is required. PHP 7.4 or higher is required.

Read the [installation.md](https://github.com/jelix/authentication-module/blob/0.4.x/docs/en/index.md) file to learn how to install the modules.


development version
----------------------

The main branch is actively developed and contains many breaking changes (API, architecture...).

Jelix 1.8.0 and higher is required. PHP 8.2 or higher is required.


Relationship with jAuth
-----------------------

jAuth and related modules (jcommunity, jauth, jauthdb, jauthdb_admin) are provided (except jCommunity) with 
the Jelix framework. They implement authentication and account management for years. But they lack of flexibility, and
it is difficult to extend them with modern authentication protocols like OAuth2, OpenID Connect, or token based authentication for API...

Jelix Authentication aims to replace them, with a better flexible architecture and more features. 

jAuth and Jelix Authentication are not working together and are not compatible.


Features
--------

Main features that are implemented for now :

- Support of several authentication/identity provider at the same time. 
- support of second factor authentication (provided by third-party modules)
- support of additional pages to show just after the authentication (provided by third-party modules). These pages can 
  be pages like to acknowledge new usage policy, to force to change the password, or just displays important information 
  to read before to enter into the application.
- email notifications when a user authenticates on the application
- the authloginpass module provides an identify provider based on a login / password authentication.
  - different builtin backends : database (dao), ldap, ini file
  - password reset feature on the login form
  - cli commands to manage logins, passwords, backends
- support for application accounts, although authentication can work without user accounts for simple applications.
  A builtin module allows implementing application accounts, but it can be replaced by your own account module. 
- The builtin account module provides
  - a page allows the user to show and modify its profile (name, email, and any other information
    if the application provides its own form and dao)
  - cli commands to manage accounts
  - notify users by email each time someone is authenticated with their account


Highly configurable :

- many events allow third-party modules to hook at different steps of the authentication (and logout)
- any third-party module can provide 
  - an identity provider using a specific protocol (saml, oauth etc)
  - its own session manager
  - its own account manager
  - a backend for the authloginpass module

User interface :

- Provides a login page on which all identity provider can add content automatically (HTML content that can be a form, a button, a link...), so the user can choose which one to use.
- an admin module to manage accounts