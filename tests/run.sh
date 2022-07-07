#!/bin/sh

XDEBUG_MODE=coverage
export XDEBUG_MODE
rm -rf html/*
phpunit --whitelist ../ \
	--coverage-html html/ \
	--bootstrap src/bootstrap.php \
	--strict-coverage \
	--testdox \
	src/

