This is an application to test the module.

A docker configuration is provided to launch the application into a container.

To launch containers the first time:

```
cd docker-conf
./setup.sh
cd ..
docker-compose build
docker-compose up -d
./dockerappctl.sh ldapreset
```

You can execute some commands into the php container, by using this command:

```
./dockerappctl.sh <command>
```

Available commands:

* `reset`: to reinitialize the application 
* `composer_update` and `composer_install`: to update PHP packages 
* `clean_tmp`: to delete temp files 
* `install`: to launch the Jelix installer


You can view the application at `http://localhost:8028` or at `http://jelixauth.local:8028`
if you set `127.0.0.1 jelixauth.local` into your `/etc/hosts`.
