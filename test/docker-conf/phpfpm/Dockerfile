ARG php_version=8.1

FROM 3liz/liz-php-fpm:${php_version}

ARG php_version
ARG DEBIAN_FRONTEND=noninteractive
ENV PHP_VERSION=${php_version}

RUN apt-get update &&  \
    apt-get upgrade -y && \
    apt-get -y install  \
    ldap-utils \
    openssl \
    php${PHP_VERSION}-xdebug \
    && \
    apt-get clean

RUN set -eux; \
    mkdir -p /etc/openldap/ /etc/ssl/ldap/; \
    chmod 755 /etc/openldap/ /etc/ssl/ldap/;

COPY profile.start /etc/profile.d/start
COPY ldap.conf /etc/openldap/ldap.conf
COPY jelix_entrypoint.sh /bin/entrypoint.d/
COPY appctl.sh /bin/
COPY xdebug.ini /etc/php/${PHP_VERSION}/fpm/conf.d/90-xdebug.ini
RUN chmod 755 /bin/entrypoint.d/jelix_entrypoint.sh /bin/appctl.sh

WORKDIR /jelixapp/
