#!/bin/sh
# Jason Brillante "Damdoshi"
# Pentacle Technologie 2009-2021
#
# La Caverne Aux Lapins Noirs
# DocBuilder

# Le nom du programme de sortie
OUTPUT="docbuilder"

# Dépendences à résoudre par le gestionnaire de paquet
APT="
  make
  gcc
  g++
  git
  texlive texlive-lang-french lexlive-latex-extra
  chromium
  libtidy-dev
  php-tidy
  pdftk
"
# Dépendences à résoudre depuis github
GIT="
  mergeconf;https://github.com/La-Caverne-aux-Lapins-Noirs/BunnyConfiguration
"

PIP="
  Pygments
"

# Si on est pas sudo, on passe sudo
if [ `id -u` -ne 0 ]; then
    sudo $0
    exit
fi

# Les verifications-installations des dépendances
for var in $APT; do
    which $var > /dev/null && echo "$var: Already installed." || (echo "$1: Starting installation:" && yes | apt-get install $var)
done
for var in $GIT; do
    NAME=`echo $var | cut -d ';' -f 1`
    URL=`echo $var | cut -d ';' -f 2`
    DIR=`echo $var | rev | cut -d '/' -f 1 | rev`
    which $NAME > /dev/null && echo "$NAME: Already installed." \
	    || (cd /tmp/ \
		    && git clone $URL \
		    && cd $DIR \
		    && make \
		    && make install \
	)
done
for var in $PIP; do
    which $var > /dev/null && echo "$var: Already installed." || (echo "$1: Starting installation:" && yes | pip install $var)
done

mkdir -p /opt/technocore/docbuilder/
cp -r src/* /opt/technocore/docbuilder/
cp docbuilder /usr/bin/docbuilder && chmod +x /usr/bin/docbuilder

exit
