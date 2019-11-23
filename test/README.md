This is an application to test the module.

A docker configuration is provided to launch the application into a container.

To launch containers the first time:

```
cd docker-conf
./setup.sh
cd ..
docker-compose build
docker-compose up -d

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
