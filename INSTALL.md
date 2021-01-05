
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
```

Read [the documentation](docs/en/index.md) to know how to configure it in details.

Activate the modules
====================

After configuring the modules, you should launch the installer to activate
them into the application:

```bash
php install/installer.php
```

You are ready to use the authentication API.
