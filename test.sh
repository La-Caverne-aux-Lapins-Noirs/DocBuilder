#!/bin/sh

echo "\033[00m"

./docbuilder \
    -a ./test/simple/activity.dab \
    -i ./test/simple/instance.dab \
    -f PDF \
    -c ./test/docbuilder.dab \
    -d ./test/dictionnary.dab \
    -m ./test/medals/ \
    -o output.pdf

