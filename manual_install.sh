#!/usr/bin/env bash

set -euo pipefail

SRC_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PREFIX="${PREFIX:-/}"

INSTALL_DEPS=1

usage() {
    cat <<EOF
Usage:
  sudo ./install_like_apt.sh [options]

Options:
  --no-deps        N'installe pas les dépendances apt.
  --prefix PATH    Installe dans PATH au lieu de /.
                  Utile pour tester: --prefix /tmp/docbuilder-install
  -h, --help       Affiche cette aide.

Variables:
  PREFIX=/chemin   Équivalent à --prefix.

Ce script reproduit l'installation indiquée par debian/install,
mais n'enregistre pas le paquet dans la base dpkg.
EOF
}

while [ "$#" -gt 0 ]; do
    case "$1" in
        --no-deps)
            INSTALL_DEPS=0
            shift
            ;;
        --prefix)
            PREFIX="$2"
            shift 2
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            echo "Option inconnue: $1" >&2
            usage >&2
            exit 1
            ;;
    esac
done

need_file() {
    if [ ! -f "$SRC_DIR/$1" ]; then
        echo "Fichier manquant: $SRC_DIR/$1" >&2
        exit 1
    fi
}

need_dir() {
    if [ ! -d "$SRC_DIR/$1" ]; then
        echo "Dossier manquant: $SRC_DIR/$1" >&2
        exit 1
    fi
}

need_file "docbuilder"
need_dir "src"
need_dir "res"
need_dir "examples"

if [ "$PREFIX" = "/" ] && [ "$(id -u)" -ne 0 ]; then
    echo "Installation dans / : relance avec sudo." >&2
    exit 1
fi

DEST_BIN="$PREFIX/usr/bin"
DEST_LIB="$PREFIX/usr/lib/docbuilder"
DEST_SHARE="$PREFIX/usr/share/docbuilder"
DEST_RES="$DEST_SHARE/res"
DEST_EXAMPLES="$DEST_SHARE/examples"

if [ "$INSTALL_DEPS" -eq 1 ] && [ "$PREFIX" = "/" ]; then
    if command -v apt-get >/dev/null 2>&1; then
        apt-get update
        apt-get install -y \
            php-cli \
            pandoc \
            texlive-xetex \
            latexmk \
            texlive-latex-extra \
            texlive-fonts-recommended \
            texlive-lang-french \
            python3-pygments \
            ghostscript
    else
        echo "apt-get introuvable, installation des dépendances ignorée." >&2
    fi
elif [ "$INSTALL_DEPS" -eq 1 ]; then
    echo "PREFIX != / : installation apt des dépendances ignorée." >&2
fi

echo "Installation de DocBuilder depuis: $SRC_DIR"
echo "Destination:"
echo "  $DEST_BIN/docbuilder"
echo "  $DEST_LIB/"
echo "  $DEST_RES/"
echo "  $DEST_EXAMPLES/"

install -d -m 0755 "$DEST_BIN"
install -d -m 0755 "$DEST_LIB"
install -d -m 0755 "$DEST_RES"
install -d -m 0755 "$DEST_EXAMPLES"

# Nettoyage des anciennes copies installées par ce script / par le paquet.
rm -rf -- "$DEST_LIB"
rm -rf -- "$DEST_RES"
rm -rf -- "$DEST_EXAMPLES"

install -d -m 0755 "$DEST_LIB"
install -d -m 0755 "$DEST_RES"
install -d -m 0755 "$DEST_EXAMPLES"

install -m 0755 "$SRC_DIR/docbuilder" "$DEST_BIN/docbuilder"

cp -a "$SRC_DIR/src/." "$DEST_LIB/"
cp -a "$SRC_DIR/res/." "$DEST_RES/"
cp -a "$SRC_DIR/examples/." "$DEST_EXAMPLES/"

find "$DEST_LIB" "$DEST_RES" "$DEST_EXAMPLES" -type d -exec chmod 0755 {} +
find "$DEST_LIB" "$DEST_RES" "$DEST_EXAMPLES" -type f -exec chmod 0644 {} +
chmod 0755 "$DEST_BIN/docbuilder"

if [ "$PREFIX" = "/" ] && [ "$(id -u)" -eq 0 ]; then
    chown -R root:root "$DEST_BIN/docbuilder" "$DEST_LIB" "$DEST_SHARE"
fi

echo
echo "Vérification des binaires attendus :"

missing=0

check_bin() {
    if command -v "$1" >/dev/null 2>&1; then
        echo "  OK  $1"
    else
        echo "  MANQUANT  $1" >&2
        missing=1
    fi
}

check_bin php
check_bin pandoc
check_bin latexmk
check_bin xelatex
check_bin pygmentize
check_bin gs
check_bin mergeconf

echo

if [ "$missing" -ne 0 ]; then
    echo "Installation copiée, mais une ou plusieurs dépendances sont absentes." >&2
    echo "Attention en particulier à mergeconf : il n'est pas installé par la liste apt actuelle du paquet docbuilder." >&2
    exit 2
fi

echo "Installation terminée."
echo "Commande disponible : $DEST_BIN/docbuilder"
