#!/bin/sh

set -e

echo "# Installing librabbitmq ${LIBRABBITMQ_VERSION}"
git clone git://github.com/alanxz/rabbitmq-c.git
cd rabbitmq-c
git checkout ${LIBRABBITMQ_VERSION}
git submodule update --init
autoreconf -i && ./configure && make && sudo make install
