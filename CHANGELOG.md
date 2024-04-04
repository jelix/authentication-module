

Next version
------------

There is a new **account module** which add feature for user accounts into an application.
Its feature:
- page for the user to show and modify its profile (name, email, and any other informations
  if the application provides its own form and dao)
- commands to 
  - list accounts, 
  - attach/detach an account to an identity managed by an authentication provider
  - list attached identities to an account
  - create an account, or create an account with a loginpass identity
 

This new version implements also a **workflow for the authentication process**. 
The authentication process has several steps that modules can bring, allowing a high customization.
Major Steps are :
- primary authentication by an authentication provider (with a login form, or remote authentication etc..)
- retrieve of the application account corresponding to the authenticated user
- or creation of the account, if the automatic account creation is enabled
- second factor step, if a module provides a second factor for authentication
- access validation step : modules can provide additionnal steps, like   
  acknowledgment of terms of service, or a form to force to change the password, etc.
- final step: the user session is complete and the user can use the application.
 

**Other new features**:

- commands for the loginpass provider
  - to create/delete a login
  - to change a password
  - to list activated backends
  - to list logins
- support of password reset into the authloginpass module


**API BREAKAGE:**

In several places, "username" has been renamed to "realname" to avoid confusion with "login".
- in `AuthUser::ATTR_NAME`
- in dao `authloginpass~user`
- in ldap attributes


0.3.0
-----

- New notification events: `AuthenticationCanUseApp`, `AuthenticationFail`
- support of Jelix 1.8
- new adapter for jAcl2

loginpass provider:

- dbdao backend: new method `getByLoginForAuthentication` in the dao
- fix inifile: save modifications into memory.
- Support of extra session attributes into inifile backend and the dbdao backend

**API BREAKAGE**

- if you provides your own dao file for the dbdao backend, add a new method `getByLoginForAuthentication`


0.2.2
-----

- authloginpass: installation parameter to no create the table for the dbdao plugin
- correcting installation issues with composer and test autoload

0.2.1
-----

- fix typo in a method name into Manager
- fix ldap: newUserDN should be optional if featureCreateUser is off

0.2.0
------

- support of several authentication providers. The user can choose his authentication provider on the login page.
- implementation of a authentication provider 'loginpass' (module 'authloginpass'), allowing to the user to
  authenticate with a login and a password. Several backends are provided: dbdao (SQL database), inifile, ldap
- support of a session manager: you have the choice to store data about authenticated user during the session: into a PHP session, or only memory (for stateless authentication)
- a controller to sign in and sign out, displaying HTML content provided by implementation of all activated authentication provders. HTML content can be a form, a button, a link...
