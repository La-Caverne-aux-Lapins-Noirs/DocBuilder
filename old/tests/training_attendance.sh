#!/bin/sh

echo -n "\033[00m"

if [ -z "$1" ]; then
    ./$0 ./tests/training_attendance
    exit
fi

./docbuilder \
    -c ./tests/docbuilder.dab \
    -a $1/activity.dab \
    -i $1/instance.dab \
    -d ./tests/dictionnary.dab \
    -m ./tests/res/medal/ \
    -o ./tests/html/output.pdf \
    --keep-trace \
    --language FR
