# IV) Références des directives<A id="a39"></A>

Dans cette partie de la documentation sont décrites les directives avec
leur syntaxe, des notes d'utilisation et des exemples

## IV-1) Déclarer les langues: `.languages`<A id="a40"></A>

La directive `.languages` déclare les langues utilisables dans les fichiers sources en leur affectant
un code, un code ISO associé et facultatif, et en indiquant éventuellement le code *main* de la langue
*principale*.

La langue *principale* a pour seule particularité que les fichiers générés pour cette langue
auront l'extension `.md` sans le code de langue par exemple `README.md`, par opposition aux autres
langues dont les fichiers auront l'extension `.code.md`, par exemple `README.fr.md`.

### IV-1.1) Syntaxe<A id="a41"></A>

La directive `.languages` est située seule sur une ligne et est suivie d'une liste de codes
pour chacune des langues utilisées dans les fichiers sources, chaque code pouvant être associé à un
code ISO facultatif; L'un des codes peut être déclaré comme langue principale.

```code
.languages <code>[=<iso>][,...] [main=<code>]
```

Chaque `<code>` déclare une langue qui pourra être utilisée avec la directive `.<code>((` qui
ouvrira une section de texte rédigée dans cette langue.

Le paramètre facultatif `main=<code>` indique la langue principale : les fichiers générés pour ce
code de langue auront une extension simple `.md` au lieu de `.<code>.md`. Par exemple, le fichier
source `README.base.md` générera un fichier `README.md` pour la langue principale et des fichiers
`README.<code>.md` pour chacun des autres codes de langue. Ceci est utile pour les documents destinés
à des environnements qui contrôlent les fichiers Markdown déposés, comme les dépôts Git qui exigent un
fichier `README.md` en racine du dépôt.

### IV-1.2) Remarques<A id="a42"></A>

- Aucun fichier n'est généré avant qu'une directive `.languages` ait été localisée dans tous les fichiers
sources. Tout texte précédant cette directive sera ignoré.
- La directive est globale à tous les fichiers sources, elle peut donc être placée dans le premier fichier
traité. En cas de doute sur l'ordre dans lequel les fichiers seront traités, on peut placer la même directive
au début de chaque fichier sans effet indésirable. L'ordre peut également être forcé en plaçant une directive
`.topnumber` dans chaque fichier source.
- Après la directive `.languages`, le générateur se place en mode texte par défaut et enverra tout texte à
toutes les langues jusqu'à ce qu'une directive d'ouverture de langue change cela.

### IV-1.3) Exemple<A id="a43"></A>

```code
.languages en=en_US,fr main=en
```

Avec cette directive les fichiers seront générés avec une extension `.md` pour la langue du code
`en` et `.fr.md` pour la langue `fr`.

## IV-2) Définition d'un schéma de numérotation : `.numbering`<A id="a44"></A>

La directive `.numbering` définit le schéma de numérotation pour le fichier actuel et les sommaires.
La syntaxe est identique à celle du paramètre `-numbering` de la ligne de commande.

> ATTENTION : le paramètre de ligne de commande s'applique à tous les fichiers sources, tandis
que la directive permet de modifier le schéma pour le fichier où elle apparaît.

### IV-2.1) Syntaxe<A id="a45"></A>

```code
.numbering [<niveau>]:[<préfixe>]:<symbole>[:<séparateur>][,...]]
```

Voici une description des parties de la définition de niveau. Elles sont identiques aux
paramètres de la ligne de commande.

- `<niveau>` est un chiffre entre `1` and `9` facultatif qui indique le ,niveau de titre concerné (c'est le nombre de `#`
qui débute le titre). Par défaut c'est le niveau suivant celui de la définition précédente, en commençant par 1.
- `:` est un séparateur obligatoire entre chacune des parties de la définition, même pour les parties omises.
- `préfixe` est un préfixe facultatif pour le tout premier niveau de titre, par exemple `Chapitre `. Le préfixe n'apparaît
que dans le libellé des titres de premier niveau, même s'il est spécifié pour un autre niveau.
- `<symbole>` est un symbole obligatoire qui peut être une lettre majuscule `A` à `Z`, une lettre minuscule `a` à `z`,
un chiffre de `1` à `9` ou la mention spéciale `&I` ou `&i` pour représenter les chiffres romains en majuscules
ou minuscules. Ce symbole indique la valeur de départ pour la numérotation du niveau, sauf pour les chiffres romains
qui commencent toujours à `I` ou `i`.
- `séparateur` est un caractère facultatif qui sera écrit après les symboles de numérotation de ce niveau. Les symboles
séparateurs les plus courants sont `.`, `-` ou `)`. Par défaut les niveaux de la numérotation sont séparés par un `.`.
- `,` sépare les différentes définitions de niveau.

Le premier titre d'un niveau N est numéroté avec le symbole indiqué, les suivants au même niveau N seront ensuite
incrémentés jusqu'à ce que survienne un titre de niveau supérieur N-1 qui réinitialisera la prochaine séquence de
niveau N au symbole de départ.

## IV-3) Numéro de titre niveau 1 : `.topnumber`<A id="a46"></A>

La directive `.topnumber` définit le numéro du titre de niveau 1 du fichier actuel au sein du schéma de
numérotation défini par `.numbering` ou le paramètre `-numbering`de la ligne de commande. Ce numéro peut
être utiliser pour numéroter les fichiers successifs dans un sommaire général. Chaque fichier possède un
seul titre de niveau 1. Si la directive `.topnumber` est utilisée dans les fichiers, ils seront traités dans
l'ordre défini par ces directives.

### IV-3.1) Syntaxe<A id="a47"></A>

```code
.topnumber <n>
```

Le paramètre `n` est n'importe quel nombre entier à partir de 1. Chaque fichier doit avoir un numéro unique,
car l'utilisation de numéros identiques aura des effets imprévisibles.

## IV-4) Génération de sommaire : `.toc`<A id="a48"></A>

La directive `.toc`génère un sommaire à partir des niveaux de titres qui lui sont indiqués. Dans la
syntaxe Markdown, ces niveaux sont définis par le nombre de caractères `#` en début de ligne : `#` est pour
le titre de niveau 1 qui doit être unique dans le fichier, `##` pour les titres de niveau 2 etc.

Par défaut, le titre de niveau 1 est ignoré dans les sommaires car il est considéré comme le titre
du fichier, et le sommaire inclut les niveaux de titre 2 à 4. Mais le niveau 1 peut être ajouté
pour générer un sommaire global des titres de tous les fichiers. Un tel sommaire global est généralement
placé dans le fichier principal à partir duquel on peut accéder aux autres fichiers.

Le sommaire insère un lien vers chaque titre de chaque fichier qu'il inclut

> Le sommaire sera placé à l'endroit de la directive `.toc`.
> Le sommaire reçoit une ancre nommée ou identifiée `toc` qui peut être utilisée comme cible
  dans les fautes fichiers.
> Si un schéma de numérotation a été programmé avec le paramètre `-numbering` ou une directive
  `.numbering`, il sera utilisé pour les titres placés dans le sommaire.
> Le titre du sommaire est écrit comme un titre de niveau 2 dans les fichiers générés.

### IV-4.1) Syntaxe<A id="a49"></A>

La directive `.toc` doit être écrite sur une ligne isolée avec ses paramètres. La plupart du
temps elle se situe après le titre du fichier et une introduction. Un sommaire sans
aucun paramètre écrira la liste des titres `##` à `###` du fichier en cours (niveaux 2 à 4).

```code
.TOC [level=[m][-][n]] [title=<texte de titre>] [out=md|html]
```

#### IV-4.11) Paramètre `level`<A id="a50"></A>

Ce paramètre choisit les niveaux des titres qui apparaîtront dans le sommaire.

La syntaxe pour ce paramètre est `level=[m][-][n]` :

- Si ni `m` ni `n` ne sont indiqués, les niveaux 2 à 4 seront retenus.
- Si `m` seulement est fourni, seuls les titres de niveau `m` seront retenus.
- Si `m-` est fourni sans `n`, les niveaux `n`à 9 seront retenus.
- Si `m-n` est fourni, les niveaux `m` à `n` seront retenus.
- Si `-n` est fourni, les niveaux 1 à `n` seront retenus.

#### IV-4.12) Paramètre `title`<A id="a51"></A>

Ce paramètre est suivi d'un titre qui sera placé comme titre de niveau 2 (`##`))) juste
avant le sommaire.

- Le titre du texte peut utiliser les directives de langue, all, ignore et default comme
  le reste du texte.
- Si aucun titre n'est fourni, `Table Of COntents` sera écrit.
- Tout ce qui suit `title=` jusqu'à la fin de ligne ou jusqu'au paramètre `level=` est
  utilisé dans le titre.

### IV-4.2) Exemples<A id="a52"></A>

```code
.TOC level=1-3 title=2,".fr((Table des matières.)).en((Table Of Contents))"
```

Cette directive place une table des matières à partir des niveaux `#` à `##` des titres
trouvés dans tous les fichiers traités. L'ordre des fichiers est soit dirigé par les directives
`.topnumber` trouvées dans les fichiers, soit celui dans lequel les fichiers ont été créés dans
les répertoires la première fois, ce qui n'est pas facilement contrôlable.

Le titre des sommaires est `Table Of Contents` par défaut dans toutes les langues.

## IV-5) Texte pour toutes les langues : `.all((`<A id="a53"></A>

La directive `.all((` ouvre une section de texte qui sera écrite dans les fichiers de toutes
les langues déclarées dans `.languages`.

Cette directive est suspendue ou terminée lorsque l'une des conditions suivantes se présente :

- Une directive `.))` ferme la section de texte et retourne au contexte précédent.
- Une directive `.<code>((` démarre une section pour une des langues déclarées dans `.languages`.
- Une directive `.ignore((` démarre une section de texte ignoré.
- Une directive `.default((` ou `.((` démarre une section de texte par défaut.

Par défaut, tout texte en dehors des directives d'ouverture et de fermeture de langue
est considéré comme du texte par défaut écrit dans tous les fichiers des langues qui n'ont pas de
section spécifique, comme si une directive `.((` était active.

### IV-5.1) Syntaxe<A id="a54"></A>

```code
.all((
```

### IV-5.2) Exemples<A id="a55"></A>

Les directives peuvent être placées seules sur une ligne autour du texte sur lequel elles agissent :

```code
.all((
texte pour toutes les langues
.))
```

Elles peuvent également être placées n'importe où dans le texte :

```code
.fr((texte pour la langue 'fr' .all((texte pour toutes les langues.)) suite du texte pour 'fr'.))
```

Et elles peuvent être insérées dans les titres :

```code
# .en((Heading text for English .all(added text for all languages.)) heading text for English again .)) text for all languages
```

```code
# .fr((Texte du titre en français .all(added text for all languages.)) suite du texte en français .)) texte pour toutes les langues
```

Rappel : par défaut, le texte va dans les fichiers de toutes les langues qui n'ont pas de section spécifique.
Ce contexte par défaut est restauré lorsqu'aucune directive d'ouverture n'est plus active, comme c'est le cas
à la fin du titre exemple ci-dessus après la dernière directive `.))`.

## IV-6) Texte par défaut : `.((` ou `.default((`<A id="a56"></A>

La directive `.default((` ou `.((` ouvre une section dans laquelle le texte
ira dans toutes les langues qui n'auront pas de section spécifique à la suite de ce texte
par défaut.

Cette directive n'est en général pas nécessaire car elle est en permanence active si aucune
directive de langue n'a ouvert une section spécifique. Elle concerne tout le texte à venir jusqu'à
ce qu'une ouverture ou fermeture de langue soit rencontrée.

Une section de texte par défaut n'est **pas** équivalente à une section `.all((` :

- Le texte pour toutes les langues ira dans chaque fichier de chaque langue inconditionnellement.
- Le texte par défaut ira uniquement dans les fichiers des langues qui n'auront pas de section
  spécifique à la suite du texte par défaut.

La raison d'être du texte par défaut est de préparer le texte original du document et des titres
dans une langue courante comme l'anglais, puis d'ajouter les sections spécifiques à la volée tout en
disposant du texte original par défaut pour les langues qui n'auront pas encore été traduites.

### IV-6.1) Syntaxe<A id="a57"></A>

```code
.default((
```

ou :

```code
.((
```

### IV-6.2) Exemples<A id="a58"></A>

Les titres sont un cas spécial de texte par défaut car leur préfixe `#` est traité séparément
par MLMD et écrit dans les fichiers de toutes les langues, puis le contexte par défaut est restauré
pour le texte qui suit le préfixe :

```code
# .Main Title.fr((Titre principal.))
```

Ceci placera `# Main title` dans tous les fichiers générés sauf le fichier français `.fr.md`
qui recevra `# Titre principal`.

Pour les blocs de texte, le texte par défaut peut être placé juste avant les sections
spécifiques aux langues lui correspondant, ou il peut être placé explicitement entre les directives
d'ouverture de défaut et de fermeture pour supprimer toute ambiguïté. Les fins de ligne simples
sont ignorée lorsqu'elles ne séparent que les directives d'ouverture et de fermeture ce qui permet
de séparer visuellement les blocs.

Ici le texte par défaut est directement suivi par une traduction en français et il n'y
a pas besoin de spécifier une directive `.((` :
```code
This is the default original text..fr((Ceci est la traduction en français..))
```

Dans l'exemple ci-dessous, les sections de texte par défaut et spécifiques sont explicitement
marquées pour éviter toute ambiguïté :

```code
.((
This is the default original text.
.)).fr((
Ceci est la traduction en français.
.))
```

## IV-7) Texte ignoré : `.ignore((` ou `.!((`<A id="a59"></A>

La directive `.ignore` démarre une section de texte qui ne sera écrite dans
aucun fichier d'aucune langue. Elle a plusieurs utilités :

- commentaires dans les fichiers sources
- écrire les listes TODO de tâches restant à effectuer
- marquer les sections de texte encore à l'ébauche qui ne sont pas encore prêtes pour publication

Elle peut être suspendue ou terminée par :

- Une directive `.))` qui termine le texte ignoré et restaure le contexte précédent.
- Une directive `.all((` qui démarre du texte pour toutes les langues.
- Une directive `.<code>((` qui démarre le texte spécifique à une langue.
- Une directive `.((` ou `.default((` qui démarre du texte par défaut.

### IV-7.1) Syntaxe<A id="a60"></A>

```code
.ignore((
```

### IV-7.2) Exemple<A id="a61"></A>

La directive peut s'appliquer à des blocs entiers de texte :

```code
.ignore((
text to ignore
.))
```

La directive peut également se trouver à l'intérieur de texte par défaut ou d'une langue :

```code
Texte à générer .ignore((texte à ignorer.)) suite du texte à générer
# Titre pour toutes les langues .ignore((text ignoré.)) suite du titre
```

## IV-8) Texte pour une langue : `.<code>((`<A id="a62"></A>

La directive `.<code>` démarre une section de texte destinée uniquement à la langue
dont le code `<code>` a été déclaré dans la directive `.languages`.

Cette directive est suspendue ou terminée par :

- Une directive `.))` qui termine le texte ignoré et restaure le contexte précédent.
- Une directive `.all((` qui démarre du texte pour toutes les langues.
- Une directive `.<code>((` qui démarre le texte spécifique à une langue.
- Une directive `.((` ou `.default((` qui démarre du texte par défaut.
- Une directive `.ignore((` ou `.!((` qui démarre du texte ignoré.

Ls sections propres à une langue doivent être fermées par `.))`. Bien que les
sections puissent s'enchaîner il est conseillé de fermer la précédente avant d'en ouvrir
une nouvelle sans quoi il faudra toutes les fermer à la fin des sections de chaque langue.
Les exemples ci-après illustrent l'enchaînement de langues.

### IV-8.1) Syntaxe<A id="a63"></A>

```code
.<code>((
```

Dans cette syntaxe, `<code>` est l'un des codes déclarés dans `.languages` au début des 
fichiers sources. Les crochets `<` et `>` sont uniquement présents pour la notation et ne
doivent pas être saisis autour du code.

### IV-8.2) Exemples<A id="a64"></A>

La directive peut entourer du texte ou des titres :

```code
.en((
Text for English language only.
## Heading for English generated file
.))
```

Elle peut également intervenir à l'intérieur du texte ou des titres :

```code
.fr((Texte pour le fichier en Français.)).en((text for the English file.))
# .fr((Titre en Français.)).en((English Title.))
```

Il faut remarquer que le point `.` final est en réalité une partie de la directive de
fermeture `.))`. Cet effet visuel un peu trompeur peut être évité en utilisant des espaces :

```code
.fr((Un peu de texte en Français. .)).en((Some english text. .))
```

Les espaces entre des directives sont généralement du texte par défaut et restaurent le
contexte par défaut, ce qui peut avoir des effets indésirables car cela rompt la chaîne des textes
par défaut et spécifiques en cours. Pour utiliser des espaces il est donc préférable de les placer
à l'intérieur des directives ouvrantes et fermantes :

```code
.fr((Texte en Français suivi d'un espace .)).en((English text followed by a space .))
```

Comme expliqué précédemment, les sections de langues peuvent être enchaînées sans
les fermer, toutefois chacune devra finalement être refermée. La ligne suivante a le même effet
que l'exemple précédent :

```code
.fr(( Texte en Français .en(( English text .)).))
```

On remarque les deux directives de fermeture en fin de ligne.

Une section ouverte reste active jusqu'à ce qu'elle soit fermée ou qu'une autre
directive ouvrante soit trouvée. Dans l'exemple suivant, la première fermeture termine
la section `.en`, mais la section `.fr` reste active et le texte ira dans le fichier
`.fr.md`. La suite de l'exemple montre d'autres effets des ouvertures et fermetures.

```code
This text has no directive and will go in all files.
.fr(( Texte en Français .en(( English text .))
This text will only go into the french file because the opening . fr (( directive has not
been closed yet. .))
Now this text is in the `all` section and go in all files.
# .fr(( Titre en Français .en(( English Title .))
This text will only go into the french file because its opening directive has not been closed yet.
```

## IV-9) Texte échappé : `.{` et `.}`<A id="a65"></A>

Le texte peut être 'échappé' en l'entourant des directives `.{` et `.}`.

Dans le texte échappé, les directives et variables sont ignorées par MLMD qui
écrit le texte à l'identique dans les fichiers générés.

En syntaxe Markdown, le texte peut également être échappé en l'entourant d'accents inversés
multiples `, de barrières de code ``` ou de guillemets `"`. MLMD respectera ces
échappements Markdown et écrira le texte échappé avec ses marqueurs dans les fichiers générés
tout en ignorant toute variable ou directive qui pourrait s'y trouver. La différence avec les
directives d'échappement MLMD est que ces directives `.{` et `.}` ne seront pas écrites et seul
le texte échappé ira dans les fichiers générés.

## IV-10) Exemples<A id="a66"></A>

Le répertoire `Examples` contient divers fichiers sources exemples.
