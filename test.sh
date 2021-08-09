#!/bin/sh

echo "\033[00m"

if [ -z "$1" ]; then
    ./$0 ./test/simple
    exit
fi

./docbuilder \
    -a $1/activity.dab \
    -i $1/instance.dab \
    -f PDF \
    -c ./test/docbuilder.dab \
    -d ./test/dictionnary.dab \
    -m ./test/medals/ \
    -o output.pdf

