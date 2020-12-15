# MLMD - Générateur de Markdown Multilingue<A id="a1"></A>

[README <img src="https://www.countryflags.io/gb/flat/16.png">](README.md)[Documentation <img src="https://www.countryflags.io/gb/flat/16.png">](docs/MLMD.md)

MLMD génère des fichiers Markdown dans plusieurs langues à partir de fichiers sources
multilingues encodés en UTF-8 grâce à des directives placées dans les fichiers pour distinguer
les parties propres à chaque langue.

MLMD est un moyen simple pour conserver la structure d'une documentation dans des fichiers
uniques sans avoir à les dupliquer pour chaque langue, et facilite la rédaction par plusieurs
traducteurs se partageant les fichiers. Les fichiers sources multilingues sont de simples
fichiers texte et s'utilisent facilement avec les systèmes de contrôle de version comme Git

MLMD peut ajouter des sommaires (table des matières) et numéroter les titres dans les fichiers
et les sommaires. Une table des matières peut être globale et inclure les titres de tous les
fichiers en entrée ou rester locale et n'inclure que les titres de son propre fichier.

L'utilisateur contrôle les langues, la génération des sommaires et le schéma
de numérotation des titres par le biais de paramètres de la ligne de commande ou par
des directives placées dans les fichiers sources.

[Génération des fichiers et directives](Images/FileGeneration.png)

## Sommaire<A id="toc"></A>

- [MLMD - Générateur de Markdown Multilingue](#a1)
- I) [Installation](1-Installation.fr.md#a2)
  - I-1) [Version PHP](1-Installation.fr.md#a3)
  - I-2) [Emplacement de MLMD](1-Installation.fr.md#a4)
  - I-3) [Utilisation d'un alias pour lancer MLMD](1-Installation.fr.md#a5)
    - I-3.1) [Linux / macOS / OS X](1-Installation.fr.md#a6)
    - I-3.2) [Windows 10](1-Installation.fr.md#a7)
- II) [Utilisation de MLMD](2-Using.fr.md#a8)
  - II-1) [Paramètres d'exécution MLMD](2-Using.fr.md#a9)
  - II-2) [Chemin des fichiers sources](2-Using.fr.md#a10)
  - II-3) [Fichiers sources : paramètre `-i`](2-Using.fr.md#a11)
  - II-4) [Fichier principal : paramètre `-main`](2-Using.fr.md#a12)
  - II-5) [Mode de sortie html/htmlold/md/mdpure : paramètre `-out`](2-Using.fr.md#a13)
    - II-5.1) [Ancres HTML nommées : `htmlold`](2-Using.fr.md#a14)
    - II-5.2) [Ancres HTML identifiées : `html`](2-Using.fr.md#a15)
    - II-5.3) [Ancres Markdown : `md`](2-Using.fr.md#a16)
    - II-5.4) [Markdown pur : `mdpure`](2-Using.fr.md#a17)
    - II-5.5) [A propos des titres non uniques](2-Using.fr.md#a18)
  - II-6) [Numérotation des titres : `-numbering`](2-Using.fr.md#a19)
    - II-6.1) [Syntaxe](2-Using.fr.md#a20)
    - II-6.2) [Exemple](2-Using.fr.md#a21)
- III) [Rédaction des fichiers sources](3-Writing.fr.md#a22)
  - III-1) [Début de fichier source](3-Writing.fr.md#a23)
  - III-2) [Inclusion de fichiers sources](3-Writing.fr.md#a24)
    - III-2.1) [Directive d'inclusion](3-Writing.fr.md#a25)
    - III-2.2) [Numérotation des fichiers](3-Writing.fr.md#a26)
  - III-3) [Titres](3-Writing.fr.md#a27)
  - III-4) [Fin de ligne et fin de paragraphe](3-Writing.fr.md#a28)
    - III-4.1) [Notes](3-Writing.fr.md#a29)
  - III-5) [Blocs multi-lignes (listes, citations, tableaux)](3-Writing.fr.md#a30)
  - III-6) [Texte échappé](3-Writing.fr.md#a31)
  - III-7) [Texte en citation et barrières de code](3-Writing.fr.md#a32)
  - III-8) [Variables](3-Writing.fr.md#a33)
  - III-9) [Texte par défaut](3-Writing.fr.md#a34)
  - III-10) [Comment éviter les ambiguïtés](3-Writing.fr.md#a35)
  - III-11) [Directives](3-Writing.fr.md#a36)
  - III-12) [Effets immédiats et englobés](3-Writing.fr.md#a37)
  - III-13) [Valeurs et effets par défaut](3-Writing.fr.md#a38)
- IV) [Références des directives](4-Directives.fr.md#a39)
  - IV-1) [Déclarer les langues: `.languages`](4-Directives.fr.md#a40)
    - IV-1.1) [Syntaxe](4-Directives.fr.md#a41)
    - IV-1.2) [Remarques](4-Directives.fr.md#a42)
    - IV-1.3) [Exemple](4-Directives.fr.md#a43)
  - IV-2) [Définition d'un schéma de numérotation : `.numbering`](4-Directives.fr.md#a44)
    - IV-2.1) [Syntaxe](4-Directives.fr.md#a45)
  - IV-3) [Numéro de titre niveau 1 : `.topnumber`](4-Directives.fr.md#a46)
    - IV-3.1) [Syntaxe](4-Directives.fr.md#a47)
  - IV-4) [Génération de sommaire : `.toc`](4-Directives.fr.md#a48)
    - IV-4.1) [Syntaxe](4-Directives.fr.md#a49)
    - IV-4.2) [Exemples](4-Directives.fr.md#a52)
  - IV-5) [Texte pour toutes les langues : `.all((`](4-Directives.fr.md#a53)
    - IV-5.1) [Syntaxe](4-Directives.fr.md#a54)
    - IV-5.2) [Exemples](4-Directives.fr.md#a55)
  - IV-6) [Texte par défaut : `.((` ou `.default((`](4-Directives.fr.md#a56)
    - IV-6.1) [Syntaxe](4-Directives.fr.md#a57)
    - IV-6.2) [Exemples](4-Directives.fr.md#a58)
  - IV-7) [Texte ignoré : `.ignore((` ou `.!((`](4-Directives.fr.md#a59)
    - IV-7.1) [Syntaxe](4-Directives.fr.md#a60)
    - IV-7.2) [Exemple](4-Directives.fr.md#a61)
  - IV-8) [Texte pour une langue : `.<code>((`](4-Directives.fr.md#a62)
    - IV-8.1) [Syntaxe](4-Directives.fr.md#a63)
    - IV-8.2) [Exemples](4-Directives.fr.md#a64)
  - IV-9) [Texte échappé : `.{` et `.}`](4-Directives.fr.md#a65)
  - IV-10) [Exemples](4-Directives.fr.md#a66)
- V) [Correction des fichiers sources](5-Debugging.fr.md#a67)
  - V-1) [NE CORRIGEZ PAS LES FICHIERS GENERES](5-Debugging.fr.md#a68)
  - V-2) [Mode trace : `-trace`](5-Debugging.fr.md#a69)
  - V-3) [Sections non closes](5-Debugging.fr.md#a70)
  - V-4) [Mauvaises indentations](5-Debugging.fr.md#a71)
  - V-5) [Langue erronée](5-Debugging.fr.md#a72)
  - V-6) [Texte par défaut mal placé](5-Debugging.fr.md#a73)
  - V-7) [Listes et tableaux Markdown incohérents](5-Debugging.fr.md#a74)
  - V-8) [Mauvaise numérotation des titres](5-Debugging.fr.md#a75)
  - V-9) [Disparition de point final](5-Debugging.fr.md#a76)
  - V-10) [Orthographe](5-Debugging.fr.md#a77)
- VI) [Conclusion](6-Conclusion.fr.md#a78)

J'espère que MLMD vous aidera à maintenir facilement vos documentations multilingues.

En ces jours de Covid, prenez soin de vous et des autres

Francis Piérot

Août-Décembre 2020<br />
A mon papa Serge, 1932-2020<br />
Il m'a appris qu'un ingénieur travaille dur à travailler moins.

[README <img src="https://www.countryflags.io/gb/flat/16.png">](README.md)[Documentation <img src="https://www.countryflags.io/gb/flat/16.png">](docs/MLMD.md)
