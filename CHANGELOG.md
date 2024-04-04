
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
