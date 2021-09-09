#!/bin/sh

XDEBUG_MODE=coverage
rm -rf html/*
phpunit --whitelist ../ \
	--coverage-html html/ \
	--bootstrap src/bootstrap.php \
	--strict-coverage \
	src/

