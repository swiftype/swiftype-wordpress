#!/bin/bash
set -e

argv0=$(echo "$0" | sed -e 's,\\,/,g')
generatordir=$(dirname "$(readlink "$0" || echo "$argv0")")

use_winpty=0

case "$(uname -s)" in
  *CYGWIN*) generatordir=$(cygpath -w "$basedir");;
  MSYS*|MINGW*) use_winpty=1;;
  Linux) generatordir=$(dirname "$(readlink -f "$0" || echo "$argv0")");;
esac
echo $generatordir

generatordir=$(cd $(dirname $argv0) > /dev/null && cd $generatordir > /dev/null && pwd)
generaroimage=elastic/elastic-openapi-codegen-php
rootdir=`cd $(dirname $argv0)/../..; pwd`
fixerimage="herloct/php-cs-fixer"
echo $rootdir

cd ${generatordir} && docker build --target runner -t ${generaroimage} elastic-openapi-codegen-php

docker run --rm -v ${rootdir}:/local ${generaroimage} generate -g elastic-php-client \
                                                               -i /local/resources/api/api-spec.yml \
                                                               -o /local/ \
                                                               -c /local/resources/api/config.json \
                                                               -t /local/resources/api/templates

docker run --rm -v ${rootdir}:/project ${fixerimage} fix --config=.php_cs.dist
