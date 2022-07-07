
Dépendances
===========

sudo apt-get install libtidy-dev php-tidy texlive texlive-lang-french texlive-latex-extra

Utilisation du logiciel
=======================

Le TechnoCentre prend les paramètres suivantes:
 -a fichier / --activity=fichier : Le fichier de description de l'activité dont on souhaite générer un document.
 -i fichier / --instance=fichier : Le fichier de description de l'instance de l'activité. (Quel élève? Quel date?)
 -s fichier / --style=fichier : Un fichier CSS qui sera exploité pour définir certains aspects graphiques.
 -f format / --format=format : Le format de sortie du document: Web, PDF, Book, Pocket. Actuellement, seul PDF
                               est supporté.
 -o fichier / --output=fichier : Le fichier de sortie. La sortie standard sera utilisée en cas d'absence.

Organisation générale d'un fichier de description d'activité
============================================================

Voir ../evaluator/README.

Formats de documentation supporté
=================================

Les formats de documentation suppportés sont les suivants:
- Les fichiers .dab seront interpretés celon une architecture particulière. Ces
  fichiers s'intègrent dans le TechnoCentre et exploitent des modules de mise
  en page pré-existant.
- Les fichiers .md seront traités par un parseur Markdown et affiché en l'état.
- Les .htm seront traités comme tel.
- Les fichiers .php seront executé et leur sortie sera interpreté comme des .htm

Attention: les fichiers .htm et .php sont susceptibles de casser le document de sortie si ils
contiennent des erreurs.

Documentation de fonction
=========================



Architecture des fichiers DABSIC
================================

