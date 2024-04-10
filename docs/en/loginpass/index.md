
Configuration of the authloginpass module
=========================================

This module provides an identity provider using a login, and a password to
authenticate a user.

Login/password can be verified against different type of backends:

* a table of a database
* an ldap directory
* an ini file where a list of login/password are stored. 

These backends are implemented into plugins of type `authlp`.

To configure the authloginpass module and its backends, you should have
these kind of options into the `app/system/mainconfig.ini.php` configuration file.


```ini
[loginpass_idp]
backends[]=<configuration name>
backends[]=<configuration name2>

afterLogin=

[loginpass:common]
passwordHashAlgo=1
passwordHashOptions=
deprecatedPasswordCryptFunction=
deprecatedPasswordSalt=

[loginpass:<configuration name>]
backendType=<plugin name>
; other information, depending of the backend plugin

```

In the `loginpass_idp`, you should indicate one or more backend configurations.
That said that when the authentication is made, each backend will be called
one after the other, until a backend says the given login/password match.
So the order of backend configuration into `backends` is important. 

You can use authentication on several ldap servers, or several databases, or
a mix of ldap, databases etc..
 
Each backend configurations are in their own section, whose name starts with
`loginpass:` and ends with the configuration name you choose.

In each backend configurations, you have at least one `backendType` option,
indicating the name of the plugin you use. You can indicate `dbdao`, `inifile`
or `ldap`, which are plugins provided by the module. You can indicate other
plugin names, of plugins you developed or provided by other modules.

An other parameter is `backendLabel`, which contains a label, that can be used
to display into an administration page for example.

There are options that are in common of all backend configuration. They are
stored into the `loginpass:common`. You can redefine them into the backend 
configuration.

The backend configuration contains options that are specific to the plugin.

 
Options configuration of the `inifile` backend
-----------------------------------------------


```ini
[loginpass:<configuration name>]
backendType=inifile
; value is the path to the inifile
inifile="var:db/users.ini.php"
sessionAttributes=
```

The inifile should contain a section for each user, with at least these values:

```ini
[login:<user login>]
password="<hashed password>"
email="<email of the user>"
name="<name of the user>"
```

Example:

```ini
[login:admin]
password="$2y$10$DT9NlAi5bC1dx74sDw9Jc.9K5PNpePgCaBPrk4vb3vzHpJPb42Kc."
email="admin@example.com"
name=Administrator
```

It can contain more attributes. For example : 

```ini
[login:admin]
;...
role=admin
birthdayDate=
```

If you want to set these additional attributes into the session, you should set
the `sessionAttributes` parameter into the `[loginpass:<configuration name>]` section:


```ini
[loginpass:<configuration name>]
;...
sessionAttributes=role,birthdayDate
```

A special value, `ALL`, indicate to retrieve all attributes.


Options configuration of the `dbdao` backend
---------------------------------------------

There is only a `profile` parameter, a `dao` parameter and an optional `sessionAttributes`.


```ini
[loginpass:<configuration name>]
backend=dbdao
; name of the jDb connection profile to use
profile=
; selector to the dao mapped to the user table
dao="authloginpass~user"
sessionAttributes=
```

You can use any dao, but it should have at least all fields you found into the
`user` dao of the `authloginpass` module.

With `sessionAttributes`, you can indicate the properties to load into the
user object in session. It is a liste of properties separated by a comma.


```ini
[loginpass:<configuration name>]
;...
sessionAttributes=role,birthdayDate
```

A special value, `ALL`, indicate to retrieve all properties.



Options configuration of the `ldap` backend
--------------------------------------------

```ini
profile=myldap
featureCreateUser=on
featureDeleteUser=on
featureChangePassword=on
```

Example of a ldap profile into the profile.ini.php:

```ini
[ldap:openldap]
hostname=openldap
tlsMode=starttls',
port=389,
;tlsMode=ldaps',
;port=636,
adminUserDn="cn=admin,dc=tests,dc=jelix"
adminPassword="passjelix"
searchUserBaseDN="ou=people,dc=tests,dc=jelix"
searchUserFilter="(&(objectClass=inetOrgPerson)(uid=%%LOGIN%%))"
searchUserByEmailFilter="(&(objectClass=inetOrgPerson)(mail=%%EMAIL%%))"
bindUserDN="uid=%?%,ou=people,dc=tests,dc=jelix"
newUserDN="uid=%%LOGIN%%,ou=people,dc=tests,dc=jelix"
newUserLdapAttributes="objectClass:inetOrgPerson,userPassword:%%PASSWORD%%,cn:%%REALNAME%%,sn:%%REALNAME%%"
searchAttributes="uid:login,displayName:username,mail:email"
;searchGroupKeepUserInDefaultGroups=on
;searchGroupProperty=
;searchGroupBaseDN=
;passwordLdapHashAlgo=
;passwordLdapSaltLength=5
```

See [ldap configuration](ldap.md)

