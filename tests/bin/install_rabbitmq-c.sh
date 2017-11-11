#!/bin/sh

set -e
set -x

# More information about this script here
# * https://github.com/alanxz/rabbitmq-c/blob/master/.travis.yml
# * https://github.com/pdezwart/php-amqp/blob/master/provision/install_rabbitmq-c.sh

echo "# Installing librabbitmq ${LIBRABBITMQ_VERSION}"
if [ ! -d "$HOME/rabbitmq-c" ]; then
  cd $HOME
  git clone git://github.com/alanxz/rabbitmq-c.git
  cd $HOME/rabbitmq-c
else
  echo 'Using cached directory.';
  cd $HOME/rabbitmq-c
  git fetch
fi

git checkout ${LIBRABBITMQ_VERSION}

mkdir build && cd build
cmake ..
cmake --build . --target install
