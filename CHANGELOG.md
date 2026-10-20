
Next version
------------

New features:

- A user can be notified by email that someone (and himself) has been
  authenticated on the application.
- new configuration parameter `notifyAuthMode` to enable/disable this feature.
- new account admin page to manage users' accounts.
- new event `ProfileViewPageEvent` allowing adding more fields into the profile page.
- New property "Display name" on the account.

BEHAVIOR CHANGES:

- The event `AuthWorkflowStep` for the step `check_account` is now dispatched after an account has been created. 
  The event object `CheckAccountEvent` has a new method `isAccountNewlyCreated()` to know if the account has been created just before the dispatch.

New API:

- New method `jAuthentication::authenticationFail()`
- The `CheckAccountEvent` class has a new method `setAccount()` to change the account associated to the authenticated user.
- New class `Jelix\Authentication\Core\IdentityAttributes` defining standardized URI-style keys for user identity
  attributes (username, email, firstname, lastname, displayname, address, phone, etc.).
- New class `Jelix\Authentication\Account\ClaimMapping` to map identity attributes to account storage fields.

BREAKING CHANGES:

- Event `AuthWorkflowStep` for the steps `get_account` and `create_account` is replaced by an account management adapter.
  A module implementing accounts management should have an object implementing
  `Jelix\Authentication\Core\Workflow\AccountWorkflowAdapterInterface` and it must register it in the configuration.
- The class `Jelix\Authentication\Account\Manager` is not anymore a static class. It should be instantiated to access
  to its methods.
- Session cleanup is now the responsibility of the identity provider.
- `AuthUser::getUserId()` has been renamed to `AuthUser::getIdpUserId()`
- The constants `AuthUser::ATTR_LOGIN`, `AuthUser::ATTR_NAME` and `AuthUser::ATTR_EMAIL` have been removed.
  User attributes now use URI-style keys defined in the new class `Jelix\Authentication\Core\IdentityAttributes`
  (e.g. `jelix/identity/username`, `jelix/identity/emailaddress`, `jelix/identity/displayname`).
  Identity providers must use these keys when creating `AuthUser` objects.
- The LDAP backend `searchAttributes` configuration must now map to `IdentityAttributes` keys instead of
  short names. For example: `uid:jelix/identity/username,mail:jelix/identity/emailaddress` instead of
  `uid:login,mail:email`.
- The account module now uses a configurable claim-mapping (`[account:claim-mapping]` INI section) to map
  identity attributes to account fields during account creation. Default mapping is provided for the
  standard fields (username, firstname, lastname, email). 
- the interface `Jelix\Authentication\Core\IdentityProviderInterface` has a new method `getWorkflowFeatures()` 
  to return the workflow features that may change the behavior of the authentication workflow.
- The method `getRealName()` of `UserAccountInterface` has been renamed to `getDisplayName()`.

BREAKING CHANGES in events:

- no more event `AuthWorkflowStep` for the workflow steps `get_account` and `create_account`
- events `AuthenticationUserCreation` and `AuthenticationUserDeletion` are renamed to `LoginPassCreation` and `LoginPassDeletion`
- event `AuthLPCanResetPassword` is renamed to `LoginPassCanResetPassword`
- class `AuthLPCanResetPasswordEvent` is renamed to `LoginPassCanResetPasswordEvent`


0.4.1
-----

- Fix security issue with PHP session: session id must be changed after authentication
  to avoid session fixation attacks
- Fix auth_user_requests dao: replace json type by text.
  Json datatype is supported only in a future version of JelixDao.

0.4.0
-----

There is a new **account module** that adds features for user accounts into an application.

Its feature:
- page for the user to show and modify its profile (name, email, and any other information
  if the application provides its own form and dao)
- commands to 
  - list accounts, 
  - attach/detach an account to an identity managed by an authentication provider
  - list attached identities to an account
  - create an account or create an account with a loginpass identity
 

This new version implements also a **workflow for the authentication process**. 
The authentication process has several steps that modules can bring, allowing high customization.
Major Steps are :
- primary authentication by an authentication provider (with a login form, or remote authentication etc)
- retrieve of the application account corresponding to the authenticated user
- or creation of the account, if the automatic account creation is enabled
- second factor step, if a module provides a second factor for authentication
- access validation step: modules can provide additional steps, like   
  acknowledgment of terms of service, or a form to force to change the password, etc.
- final step: the user session is complete, and the user can use the application.
 

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

Changes into `BackendPluginInterface`:
- new method `public function userWithEmailExists($email)`

Changes into the dao for the dbdao backend of loginpass:
- new method `getByEmail($email)`


**CONFIGURATION CHANGES:**

- new configuration properties for the ldap backend: `searchUserByEmailFilter`

0.3.2
-----

- Fix security issue with PHP session: session id must be changed after authentication
  to avoid session fixation attacks

0.3.1
-----

- loginpass commands: fix returned values.
- fix loginpass dao: jdao doesn't support yet json type
- fix loginpass:user:create: check if user already exist

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

- if you provide your own dao file for the dbdao backend, add a new method `getByLoginForAuthentication`


0.2.2
-----

- authloginpass: installation parameter to no create the table for the dbdao plugin
- correcting installation issues with composer and test autoload

0.2.1
-----

- fix typo in a method name into Manager
- fix ldap: newUserDN should be optional if `featureCreateUser` is off

0.2.0
------

- support of several authentication providers. The user can choose his authentication provider on the login page.
- implementation of an authentication provider 'loginpass' (module 'authloginpass'), allowing the user to
  authenticate with a login and a password. Several backends are provided: dbdao (SQL database), inifile, ldap
- support of a session manager: you have the choice to store data about an authenticated user during the session: into a PHP session, or only memory (for stateless authentication)
- a controller to sign in and sign out, displaying HTML content provided by implementation of all activated authentication providers. HTML content can be a form, a button, a link...
