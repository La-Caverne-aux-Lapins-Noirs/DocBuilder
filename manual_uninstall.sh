#!/usr/bin/env bash

set -euo pipefail

PREFIX="${PREFIX:-/}"
DRY_RUN=0
REMOVE_DEPS=0
FORCE=0

usage() {
    cat <<EOF
Usage:
  sudo ./uninstall_like_apt.sh [options]

Options:
  --prefix PATH     Désinstalle depuis PATH au lieu de /.
                    Utile si installé avec --prefix /tmp/docbuilder-install
  --dry-run         Affiche ce qui serait supprimé sans rien supprimer.
  --remove-deps     Propose de supprimer les dépendances apt installées pour DocBuilder.
                    À utiliser avec prudence.
  --force           Supprime même si certains fichiers semblent appartenir à un paquet dpkg.
  -h, --help        Affiche cette aide.

Ce script retire l'installation manuelle de DocBuilder faite comme si elle venait du paquet Debian.
Il ne modifie pas la base dpkg.
EOF
}

while [ "$#" -gt 0 ]; do
    case "$1" in
        --prefix)
            PREFIX="$2"
            shift 2
            ;;
        --dry-run)
            DRY_RUN=1
            shift
            ;;
        --remove-deps)
            REMOVE_DEPS=1
            shift
            ;;
        --force)
            FORCE=1
            shift
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

if [ "$PREFIX" = "/" ] && [ "$(id -u)" -ne 0 ]; then
    echo "Désinstallation depuis / : relance avec sudo." >&2
    exit 1
fi

case "$PREFIX" in
    ""|"/usr"|"/usr/"|"/bin"|"/bin/"|"/lib"|"/lib/"|"/share"|"/share/")
        echo "PREFIX dangereux ou incohérent: '$PREFIX'" >&2
        exit 1
        ;;
esac

DEST_BIN="$PREFIX/usr/bin/docbuilder"
DEST_LIB="$PREFIX/usr/lib/docbuilder"
DEST_SHARE="$PREFIX/usr/share/docbuilder"
DEST_RES="$DEST_SHARE/res"
DEST_EXAMPLES="$DEST_SHARE/examples"

targets=(
    "$DEST_BIN"
    "$DEST_LIB"
    "$DEST_RES"
    "$DEST_EXAMPLES"
)

run() {
    if [ "$DRY_RUN" -eq 1 ]; then
        printf '[dry-run] '
        printf '%q ' "$@"
        printf '\n'
    else
        "$@"
    fi
}

exists_any=0
for target in "${targets[@]}"; do
    if [ -e "$target" ]; then
        exists_any=1
    fi
done

if [ "$exists_any" -eq 0 ]; then
    echo "Aucune installation DocBuilder trouvée dans:"
    echo "  $PREFIX"
    exit 0
fi

if [ "$PREFIX" = "/" ] && command -v dpkg-query >/dev/null 2>&1 && [ "$FORCE" -eq 0 ]; then
    owned=0

    for target in "${targets[@]}"; do
        if [ -e "$target" ]; then
            if dpkg-query -S "$target" >/dev/null 2>&1; then
                echo "Attention: '$target' semble appartenir à un paquet dpkg." >&2
                owned=1
            fi
        fi
    done

    if [ "$owned" -eq 1 ]; then
        cat >&2 <<EOF

Je refuse de supprimer ces fichiers manuellement, car ils semblent gérés par dpkg.
Si DocBuilder est installé comme vrai paquet Debian, utilise plutôt :

  sudo apt-get remove docbuilder

ou :

  sudo apt-get purge docbuilder

Pour forcer malgré tout la suppression manuelle :

  sudo ./uninstall_like_apt.sh --force

EOF
        exit 2
    fi
fi

echo "Désinstallation de DocBuilder depuis:"
echo "  $PREFIX"
echo

for target in "${targets[@]}"; do
    if [ -e "$target" ]; then
        echo "Suppression: $target"
        run rm -rf -- "$target"
    fi
done

# Nettoie /usr/share/docbuilder seulement s'il est vide.
if [ -d "$DEST_SHARE" ]; then
    if find "$DEST_SHARE" -mindepth 1 -print -quit | grep -q .; then
        echo "Conservé car non vide: $DEST_SHARE"
    else
        echo "Suppression du dossier vide: $DEST_SHARE"
        run rmdir -- "$DEST_SHARE"
    fi
fi

if [ "$REMOVE_DEPS" -eq 1 ]; then
    if [ "$PREFIX" != "/" ]; then
        echo "--remove-deps ignoré car PREFIX != /" >&2
    elif command -v apt-get >/dev/null 2>&1; then
        echo
        echo "Suppression optionnelle des dépendances apt utilisées par DocBuilder."
        echo "Attention: elles peuvent servir à d'autres logiciels."
        echo
        run apt-get remove -y \
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
        echo "apt-get introuvable, dépendances non supprimées." >&2
    fi
fi

echo
echo "Désinstallation terminée."

