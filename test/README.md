This is an application to test the module.

A docker configuration is provided to launch the application into a container.

To launch containers the first time:

```
./run-docker build
./run-docker
./appctl ldap-reset
./appctl reset-install
```

You can execute other subcommands with `./appctl <command>`.

Launch `./appctl` without parameters to have the list of possible subcommands.


You can view the application at `http://localhost:8028` or at `http://jelixauth.local:8028`
if you set `127.0.0.1 jelixauth.local` into your `/etc/hosts`.

Some login/password you can use:

* `admin` / `jelix` : user defined in the `var/db/users.ini.php`
* `john` / `passjohn` : ldap user, defined in the ldap groups `group1` and `group2`
* `jane` / `passjane` : ldap user, defined in the ldap group `group1`


Working with Xdebug
===================

Into PhpStorm
-------------

Into `File > Settings > PHP > Servers`, add a new server with the name "jelixauthsrv".
Indicate `jelixauth.local` as the host and `8028` as port. Check `use path mappings`
and indicate to map `/jelixapp/` on the root of your project.

Into `File > Settings > PHP > Debug`, indicate `9003` as port.

Into `Run > Edit Configurations`, add a new configuration of type "PHP remote debug" :
Check "filter debug connection by IDE key" then choose the server "testapp" you configured
previously and use the IDE key "PHPSTORM"

To debug the testapp web site :

1. In your browser, install the xdebug extension, and indicate "PHPSTORM" as IDE key.
2. Go to `http://jelixauth.local:8028` and activate the xdebug extension
2. Into PHPStorm, add breakpoints, and activate the debug listener
4. browse testapp on pages concerned by your breakpoints, PHPStorm should halt the execution on these breakpoints.

To debug cli scripts,

1. Into PHPStorm, add breakpoints, and activate the debug listener
2. Go into the phpfpm container (`./app-ctl shell`)
3. Launch commands:
    - for installer.php, configurator.php and dev.php scripts, launch them with the `--xdebug` option
    - for any other scripts, type `export XDEBUG_SESSION=1` before launching them.

Into VisualStudio Code
----------------------

- Install the plugin php-xdebug into VSC
- Create the `.vscode/launch.json` with this content:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "JelixAuthentication web",
      "type": "php",
      "request": "launch",
      "pathMappings": {
        "/jelixapp/": "${workspaceFolder}/"
      },
      "port": 9003,
      "xdebugSettings": {
        "max_data": 1024,
        "max_depth": 5
      }
    },
    {
      "name": "Testapp CLI",
      "type": "php",
      "request": "launch",
      "pathMappings": {
        "/jelixapp/": "${workspaceFolder}/"
      },
      "port": 9003,
      "xdebugSettings": {
        "max_data": 1024,
        "max_depth": 5
      }
    }
  ]
}
```

- restart VSC
- You can now add breakpoint into your code
- in the "run and debug" tab, click on "listen for xdebug"
- browse the website, and then the code will halt on your break points
