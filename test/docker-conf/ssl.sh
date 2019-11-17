#!/bin/bash

if [ "$1" == "" -o "$2" == "" ]; then
  echo "Usage: ssl.sh <command> <domain>"
  echo "or ssl.sh build, to build the container"
fi

docker image inspect jelix-openssl >/dev/null 2>&1
if [ "$?" == "1" ]; then
  docker build -t jelix-openssl openssl/
fi

docker run -it -v $(pwd)/certs:/sslcerts --user $(id -u):$(id -g) --env CERT_DOMAIN=$2  jelix-openssl $1
