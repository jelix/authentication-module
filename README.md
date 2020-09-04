Authentication modules
======================

Core modules to bring authentication feature into a Jelix application.

These modules will replace jauth and jauthdb modules that are still provided
into Jelix. It proposes a new architecture for authentication components.

This is a work in progress.

Jelix 1.7.2 and higher are required.


Read the [INSTALL.md](INSTALL.md) file to know how to install the modules.
  
What these modules don't do:

- they doesn't manage user accounts in an application
- they doesn't manage rights 


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

Activate the module
===================

After configuring the modules, you should launch the installer to activate 
them into the application:

```bash
php install/installer.php
```

You are ready to use the authentication API.
