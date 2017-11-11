#!/bin/sh

set -e
set -x

echo "# Installing php-amqp ${PHPAMQP_VERSION}"
if [ ! -d "$HOME/php-amqp" ]; then
  cd $HOME
  git clone git://github.com/pdezwart/php-amqp.git
  cd $HOME/php-amqp
else
  echo 'Using cached directory.';
  cd $HOME/php-amqp
  git fetch
fi

git checkout ${PHPAMQP_VERSION}

phpize --clean && phpize && ./configure && make install
echo "extension = amqp.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
