
Propos
======

## Aspect général

DocBuilder sert à produire des documents divers.

Son format est un hybride basé sur Dabsic comme langage interface, suivi d'un dialecte comprenant :
- Des balises spécifiques [#Command;Parametre;Parametre] qui lui sont propres
- Et un mécanisme de compilation.

Dabsic sert à collecter et résoudre les informations néccessaire à la génération d'un document, et une fois résolu, le dialecte est généré. Ce dialecte est interprété par DocBuilder lui-même puis compilé ensuite par l'un des modules présent dans DocBuilder.

## Mécanismes de compilation

A l'heure actuelle, un seul module existe, il s'agit d'un module permettant la génération d'un pdf en exploitant **pandoc** et **xelatex**. Il est donc possible d'écrire du **markdown** et  du **latex** dans un texte géré par DocBuilder.

Dependences
===========

Pour Debian :

sudo apt-get install libtidy-dev php-tidy texlive texlive-lang-french texlive-latex-extra chromium-browser pdftk python3-pygments

**mergeconf** de la **LibLapin**.

Usage
=====

    ./docbuilder [-i ... | -m ...]+ [-o ...]? [-d]?

    -i [file]+		Configuration files
    -m [address=value]+	Edit fields of previously loaded configuration
    -o output_file	Output file
    -d			Print on stdout generated document before compilation

Format de fichier
=================

- Les formats gérés par DocBuilder sont tous ceux supporté par la LibLapin.
- Le format recommandé est le fichier Dabsic en .dab - il est le seul testé.

