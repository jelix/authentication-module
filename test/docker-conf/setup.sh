#!/bin/bash

./ssh.sh createCA tests.jelix
./ssh.sh createCert ldap.jelix tests.jelix

#cp certs/ldap.* openldap/certs/
#cp certs/tests.jelix-CA.* openldap/certs/
