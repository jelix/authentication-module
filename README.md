Jelix Authentication
====================

It is an authentication framework for Jelix Applications.
It provides a sets of features and API to implement authentication that match your needs.
It brings also an account management. However, it doesn't manage authorizations or rights.

This is a work in progress.

Jelix 1.8.0 and higher is required.

Read the [installation.md](docs/en/installation.md) file to learn how to install the modules.


These modules will deprecate jauth and jauthdb modules that are still provided
into Jelix.

Features
--------

Main features :

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
- support for application accounts, although authentication can work without user accounts for simple applications. A builtin module allows to implement application accounts, but it can be replaced by your own account module. 
-  The builtin account module provides
  - a page allowing the user to show and modify its profile(name, email, and any other information
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