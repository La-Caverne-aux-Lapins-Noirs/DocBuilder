
Dependencies
============

sudo apt-get install libtidy-dev php-tidy texlive texlive-lang-french texlive-latex-extra chromium-browser pdftk

Usage
=====

Type ./docbuilder alone to display information about how to use docbuilder.

Format of documentation
=======================

- .dab files are gonna be fully interpreted by docbuilder. It is its main format.
- .md are gonna be interpreted as markdown.
- .htm are gonna be interpreted as HTML.
- .php are gonna be interpreted as PHP et their output interpreted as HTML.

Pay attention with .htm and .php as they can break the output of the document
if they contain errors.

