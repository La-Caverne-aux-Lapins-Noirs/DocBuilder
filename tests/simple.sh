#!/bin/sh

echo "\033[00m"

if [ -z "$1" ]; then
    ./$0 ./tests/simple
    exit
fi

./docbuilder \
    -a $1/activity.dab \
    -i $1/instance.dab \
    -t training_attendence \
    -c ./tests/docbuilder.dab \
    -d ./tests/dictionnary.dab \
    -m ./tests/res/medal/ \
    -o output.pdf \
    --keep-trace \
    --language FR
