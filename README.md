
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
    ./docbuilder [-i ... | -m ...]+ [-o ...]? [-d]? [--blank]? [--hash-only]? [--hash-file file]?

    -i [file]+		Configuration files
    -m [address=value]+	Edit fields of previously loaded configuration
    -o output_file	Output file
    -d			Print on stdout generated document before compilation
    --blank		Allow missing required signatories for a blank template
    --hash-only	Print   DocBuilder.DabsicHash without rendering
    --hash-file file	Write DocBuilder.DabsicHash to a companion file

Format de fichier
=================

- Les formats gérés par DocBuilder sont tous ceux supporté par la LibLapin.
- Le format recommandé est le fichier Dabsic en .dab - il est le seul testé.


Signataires génériques
======================

Un modèle peut déclarer les rôles de signature dont il a besoin dans le scope
`Signatures`. Les noms de rôles sont des symboles Dabsic / identifiants C
stricts :

    [Signatures
      [Director
        Required = 1
      ]
      [Student
        Required = 1
      ]
    ]

Les fichiers de configuration décrivant les personnes restent indépendants du
modèle. N'importe quel scope injecté peut se déclarer signataire :

    [Person
      Identity = "Alice Dupont"
      Mail = "alice@example.org"
      Signatory = 1
      As = "Student"
    ]

Après la fusion Dabsic effectuée par `mergeconf`, DocBuilder expose ce scope
sous `Signatories.Student`. Les documents existants peuvent donc continuer à
lire `Signatories.<Role>` sans connaître le nom ni l'emplacement initial du
scope décrivant la personne.

`As` peut également être un tableau Dabsic de chaînes. Une même personne est
alors exposée sous chacun de ses rôles :

    [Person
      Identity = "Bob Exemple"
      Signatory = 1
      {As
        "Director",
        "Finance"
      }
    ]

Le même scope est alors disponible sous `Signatories.Director` et
`Signatories.Finance`.

Si `Signatures` existe, tout rôle annoncé par `As` doit y être déclaré. Un rôle
`Required = 1` doit avoir exactement un signataire. Deux personnes différentes
ne peuvent pas revendiquer le même rôle. Les erreurs sont signalées avant le
rendu du document.

Le scope explicite historique `Signatories` reste temporairement accepté pour
permettre la migration des anciens modèles et appels. Lorsqu’un rôle est aussi
déclaré dans `Signatures`, les métadonnées du rôle définies par le document
(par exemple `Role`) sont fusionnées avec l’identité injectée. Il est destiné à
être supprimé lorsque les producteurs et les modèles auront tous migré vers
`Signatures` + `Signatory` / `As`.

Mise en page des tableaux
==========================

`Table` conserve par défaut son comportement historique compact : le tableau
prend la largeur minimale calculée par Pandoc et reste centré.

    [@Table;2;
    | Libellé | Valeur |
    |:--|:--|
    | Prix | 100 € |
    ]

Pour les fiches et formulaires, le mode `Wide` occupe toute la largeur utile :

    [@Table;2;
    | Élément | Réponse |
    |:--|:--|
    | Projet | ... |
    ;Wide]

Un dernier argument optionnel donne les proportions relatives des colonnes :

    [@Table;2;
    | Élément | Réponse |
    |:--|:--|
    | Projet | ... |
    ;Wide;60,40]

Les proportions doivent contenir exactement autant de nombres positifs que le
tableau possède de colonnes. `Full` est accepté comme synonyme de `Wide` et
`Minimal` comme synonyme de `Compact`.

### Generic layout helpers

The LaTeX engine also exposes two lightweight helpers useful for administrative forms:

- `[@TextBox;4;content]` draws a full-width framed text area 4 cm high.
- `[@Row;3;"1,2,1";left;center;right]` lays out independent cells on one row using relative widths.

`Generic` documents use zero paragraph indentation so form fields align consistently below section headings.


Mise en page des lettres
========================

Le moteur `Letter` accepte un scope `Letter` pour ajuster les zones fixes de
la première page sans écrire de LaTeX dans le modèle :

    [Letter
      HeaderHeight = "4cm"
      FromY = "4cm"
      TargetY = "4cm"
      DateX = "11.5cm"
      DateY = "8cm"
      DateWidth = "7cm"
      BodyGap = "2cm"
    ]

`FromX`, `FromY`, `FromWidth`, `TargetX`, `TargetY`, `TargetWidth`,
`BodyLeft` et `BodyRight` restent également configurables. `HeaderHeight`
modifie la hauteur réservée à l'en-tête. Lorsqu'un `DateY` est fourni, la date
est rendue dans un bloc indépendant du destinataire afin de pouvoir la déplacer
verticalement sans déplacer l'adresse. Sans `DateY`, le comportement historique
est conservé : la date suit le destinataire dans le même bloc.

Une lettre peut aussi définir un champ `Subject`. Dans ce cas, `Letter` traite
le document comme un courrier structuré : le sujet est rendu en gras dans le
flux principal, puis `Date` est placée juste en dessous, alignée à droite, avant
`Content`. Ce mode est préférable aux coordonnées absolues pour l'objet et la
date, car ils restent liés au début réel du corps de lettre. `DateY` conserve
son rôle pour les anciens modèles qui ne définissent pas `Subject`.


Generation vierge
=================

L'option `--blank` permet de produire une version vierge / imprimable d'un
document sans imposer la presence des roles declares `Required = 1` :

    docbuilder --blank -i contrat.dab -o contrat-vierge.pdf

Sans `--blank`, l'absence d'un signataire obligatoire reste une erreur. En mode
vierge, `Signatories.<Role>` est tout de meme cree a partir des metadonnees de
`Signatures.<Role>` ; aucune identite n'est inventee. Si une identite signataire
est fournie, elle reste resolue normalement.

Empreinte Dabsic
================

DocBuilder calcule automatiquement `DocBuilder.DabsicHash`, une empreinte
SHA-256 de la configuration Dabsic entierement fusionnee et resolue par
`mergeconf`. Le champ `DocBuilder.DabsicHash` est retire du calcul afin que
l'empreinte ne depende jamais d'elle-meme. Les scopes associatifs sont tries
par cle pour rendre le calcul canonique ; l'ordre des tableaux Dabsic reste
significatif.

Le modele peut afficher l'empreinte au moment du rendu :

    [#Variable;DocBuilder.DabsicHash]

Deux options permettent aussi de l'exploiter sans analyser le PDF :

    docbuilder --hash-only -i document.dab
    docbuilder --hash-file document.sha256 -i document.dab -o document.pdf

`--hash-only` affiche uniquement l'empreinte et ne lance pas le moteur de
rendu. `--hash-file` ecrit l'empreinte effectivement utilisee par DocBuilder
dans un fichier compagnon tout en poursuivant la generation normale.

Rotation d'image pseudo-aleatoire
=================================

La directive `Image` accepte l'option DocBuilder `random_angle`. Une amplitude
unique est interpretee symetriquement, et une plage peut etre donnee avec
`min:max` :

    [@Image;stamp.png;width=4cm;random_angle=20]
    [@Image;stamp.png;width=4cm;random_angle=-20:20]

L'angle est derive de `DocBuilder.DabsicHash`, de l'image et de son occurrence
dans le document. Il varie donc d'un document a l'autre mais reste identique
lorsqu'un meme Dabsic resolu est regenere, ce qui conserve la reproductibilite.
Pour une rotation autour du centre, `origin=c` est ajoute automatiquement. Une
image ne peut pas combiner `angle=` et `random_angle=`.
Alignement et alinéas
=====================

Le moteur LaTeX fournit des directives de bloc pour aligner du contenu :

    [@Left;Texte aligné à gauche]
    [@Center;Texte centré]
    [@Right;Texte aligné à droite]

Ces formes avec contenu restent compatibles avec le Markdown placé dans le
bloc. Par exemple, le gras et les listes sont encore interprétés par Pandoc :

    [@Center;**Titre important**]

    [@Left;
    - premier élément
    - deuxième élément
    ]

Les formes sans contenu sont aussi disponibles comme déclarations LaTeX :
`[@Left]`, `[@Center]`, `[@Right]`. Elles agissent sur la suite du groupe LaTeX
courant ; pour un contenu isolé, préférer la forme avec argument ci-dessus.

Pour forcer un alinéa au début d'un paragraphe :

    [@Indent]Voici un paragraphe avec un retrait de première ligne de 1 cm.

Une largeur en centimètres peut être indiquée :

    [@Indent;1.5]Voici un retrait de 1,5 cm.

`Indent` ne modifie pas la valeur globale de `\\parindent` : il ajoute seulement
le retrait à l'endroit où la directive est utilisée.
