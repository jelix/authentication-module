This is an application to test the module.

A docker configuration is provided to launch the application into a container.

To launch containers the first time:

```
./run-docker build
./run-docker
./appctl ldapreset
./appctl reset-setup
```

You can execute some commands into the php container, by using this command:

```
./appctl <command>
```

Available commands:

* `reset`: to reinitialize the application 
* `reset-setup`: to reinitialize the application and setup first data 
* `composer-update` and `composer-install`: to update PHP packages 
* `clean_tmp`: to delete temp files 
* `install`: to launch the Jelix installer
* `ldapreset`: to restore default users in the ldap
* `ldapusers` : to show users defined in the ldap
* `unit-tests` : to launch unit-tests


You can view the application at `http://localhost:8028` or at `http://jelixauth.local:8028`
if you set `127.0.0.1 jelixauth.local` into your `/etc/hosts`.

Some login/password you can use:

* `admin` / `jelix` : user defined in the `var/db/users.ini.php`
* `john` / `passjohn` : ldap user, defined in the ldap groups `group1` and `group2`
* `jane` / `passjane` : ldap user, defined in the ldap group `group1`
