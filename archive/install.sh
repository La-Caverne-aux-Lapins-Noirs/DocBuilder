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
  chromium-browser
  libtidy-dev
  php-tidy
"
# Dépendences à résoudre depuis github
GIT="
  mergeconf;https://github.com/La-Caverne-aux-Lapins-Noirs/BunnyConfiguration
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

mkdir -p /opt/docbuilder/
cp -r src/* /opt/docbuilder/
cat > /usr/bin/docbuilder <<EOF
#!/usr/bin/env php
<?php
require_once ("/opt/docbuilder/docbuilder.php");
EOF
chmod +x /usr/bin/docbuilder

exit
# Etait neccessaire avant
apt-get install wkhtmltopdf
apt-get install xvfb
printf '#!/bin/bash\nxvfb-run -a --server-args="-screen 0, 1024x768x24" /usr/bin/wkhtmltopdf -q $*' > /usr/bin/wkhtmltopdf.sh
chmod a+x /usr/bin/wkhtmltopdf.sh
ln -s /usr/bin/wkhtmltopdf.sh /usr/local/bin/wkhtmltopdf

wkhtmltopdf --encoding utf-8 ".
	      	        "-B 0 -T 0 -L 0 -R 0 --page-width 15.75cm --page-height 23cm ".
	       "--title '".Translate($DocBuilder->Activity["Activity"])."' ".
	       "'$tmp' '$DocBuilder->OutputFile'
