
Setting up the modules
======================

The best method is to use Composer.

In a commande line inside your project, execute:

```
composer require "jelix/authentication-module"
```

Launch the configurator for your application to enable the module

```bash
php dev.php module:configure authcore
php dev.php module:configure authloginpass
php dev.php module:configure account
```

Read [the documentation](index.md) to know how to configure it in details.

Activate the modules
====================

After configuring the modules, you should launch the installer to activate
them into the application:

```bash
php install/installer.php
```

You are ready to use the authentication API.


Create the first account
========================

You must create at least one application account :

```shell
  php console.php account:create admin admin-test@jelix.org Bob SuperAdmin
```

Then you setup an authentication method for this account. 

To list configured backend to set a login/password, run the command

```
$ php console.php loginpass:backend:list

+----------------+----------------+
| Id             | Name           |
+----------------+----------------+
| ldap           | ldap           |
| daotablesqlite | daotablesqlite |
| inifile        | Native users   |
+----------------+----------------+
```

To create a login and a password that will be stored using the backend `inifile`
(so into an ini file).

```shell
  php console.php account:login:create admin --backend=inifile --set-pass=jelix
```
