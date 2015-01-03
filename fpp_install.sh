#!/bin/bash
pushd $(dirname $(which $0))
apt-get -y update
apt-get -y install php5-curl php5-common
popd
