
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
permettre la migration des anciens modèles et appels. Il est destiné à être
supprimé lorsque les producteurs et les modèles auront tous migré vers
`Signatures` + `Signatory` / `As`.
