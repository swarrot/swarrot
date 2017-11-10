#!/bin/sh

set -e
set -x

# More information about this script here
# * https://github.com/alanxz/rabbitmq-c/blob/master/.travis.yml
# * https://github.com/pdezwart/php-amqp/blob/master/provision/install_rabbitmq-c.sh

echo "# Installing clang"
wget -O - http://apt.llvm.org/llvm-snapshot.gpg.key | sudo apt-key add -
sudo apt-add-repository "deb http://apt.llvm.org/trusty/ llvm-toolchain-trusty-3.9 main"
sudo apt-get -q update;
sudo apt-get install -y clang-3.9 libpopt-dev;

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
cmake .. -DCMAKE_INSTALL_PREFIX=/usr -DCMAKE_INSTALL_LIBDIR=lib
sudo cmake --build . --target install
