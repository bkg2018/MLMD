# II) Utilisation de MLMD<A id="a8"></A>

MLMD est exécuté par l'interpréteur PHP et reçoit des paramètres qui lui indiquent
les fichiers à traiter ou explore le répertoire actuel et ses sous-répertoires pour localiser
les fichiers à traiter.

D'autres paramètres indiquent à MLMD où placer les fichiers générés, comment numéroter les titres
ou écrire les liens.

## II-1) Paramètres d'exécution MLMD<A id="a9"></A>

La syntaxe pour `mlmd.php` et ses paramètres est la suivante :

```code
php <chemin-de-mlmd>mlmd.php [paramètres]
paramètres :
    [-i <chemin_fichier> [...]]
    [-main <chemin_fichier>]
    [-out html|htmlold|md|mdpure]
    [-numbering <schéma_numérotation>]
    [-od <chemin>]
```

Si un alias a été créé, il est inutile d'appeler explicitement l'interpréteur php ou de donner
le chemin complet du script MLMD.

Les fichiers sources peuvent être spécifiés avec le paramètre `-i`, ou bien être trouvés
automatiquement par le script si aucun `-i` n'est spécifié. Cela est décrit dans
[Fichiers sources](#fichiers-sources-parametre--i)

## II-2) Chemin des fichiers sources<A id="a10"></A>

.Le noms des fichiers sources MLMD doivent posséder l'extension `.base.md`ou `.mlmd`. Les fichiers
avec une extension différente seront ignorés par MLMD. L'extension `.base.md` peut être pratique car la
coloration syntaxique Markdown fonctionnera dans la plupart des éditeurs de texte pour la majorité des
fichiers, toutefois les fichiers sources MLMD ne sont pas réellement des fichiers Markdown, ce qui peut
mener à une certaine confusion lors de l'édition. L'extension `.mlmd` est plus explicite et montre clairement
que les fichiers sont à destination de MLMD, et les éditeurs de texte peuvent généralement être paramétrés
pour reconnaître la syntaxe MLMD.

Lorsqu'aucun paramètre `-i` n'est fourni, MLMD explore le répertoire de départ et génère des fichiers
pour chacun des fichiers `.mlmd` ou `.base.md` qu'il y trouvera, en respectant la même hiérarchie de répertoires.

Le paramètre `-main` indique le fichier principal et le répertoire racine pour tous les liens relatifs
qui seront placés dans les fichiers générés. Le répertoire de ce fichier est considéré comme la racine de
la hiérarchie de l'ensemble des fichiers. Aucun fichier situé au dessus de ce répertoire ou dans une autre
branche ne sera retenu, et tous les liens et noms de fichiers ou de répertoires seront relatifs à cette racine.

Les différentes directives sont décrites dans la
partie [Directives](#directives).

## II-3) Fichiers sources : paramètre `-i`<A id="a11"></A>

Pour traiter des fichiers spécifiques, on utilise le paramètre `-i` suivi d'un chemin de fichier. Pour traiter
plusieurs fichiers il est préférable de les placer dans un arbre de sous-répertoires et de démarrer MLMD à partir de la racine
de cet arbre de répertoires afin qu'il trouve de lui-même tous les fichiers sources. le paramètre `-i` est alors inutile

- Traiter un fichier donné : `-i <chemin>` :

  ```code
  php ~/phpscripts/mlmd.php -i ~/project/README.mlmd
  ```

- Traiter plusieurs fichiers : utiliser plusieurs `-i` :

  ```code
  php ~/phpscripts/mlmd.php -i ~/project/README.mlmd -i ~/project/HOWTOUSE.mlmd
  ```

- Traiter un répertoire et ses sous-répertoires : se placer dans ce répertoire et ne pas donner de paramètre `-i`:

  ```code
  cd ~/project
  php ~/phpscripts/mlmd.php
  ```

  Cette syntaxe traite tous les fichiers d'extension `.mlmd` ou `.base.md` trouvés dans le répertoire
  et ses sous-répertoires. Les autres fichiers sont ignorés.

## II-4) Fichier principal : paramètre `-main`<A id="a12"></A>

Si un fichier se nomme `README.mlmd`dans le répertoire de démarrage de MLMD, il est considéré
comme le fichier principal et les chemins et liens générés seront relatifs à l'emplacement de ce
fichier. Attention à la casse du nom : `README` est en majuscules tandis que `.mlmd` est en minuscules.
Sur Windows les majuscules ne sont pas significatives mais MLMD cherchera tout de même `README`
en majuscules.

S'il n'y a aucun fichier `README.mlmd` dans le répertoire de démarrage, le paramètre `-main`
peut être utilisé pour indiquer le fichier source principal et, indirectement, le répertoire racine
de tous les liens relatifs :

```code
php ~/phpscripts/mlmd.php -main ./main.mlmd
```

Le fichier principal est en général l'endroit le plus approprié pour insérer un sommaire
global qui couvre tous les fichiers. Voir la directive `.toc` pour plus de détails.

## II-5) Mode de sortie html/htmlold/md/mdpure : paramètre `-out`<A id="a13"></A>

Le paramètre `-out` choisit la syntaxe HTML ou Markdown pour les liens
générés dans les sommaires.

Dans les fichiers Markdown on dispose de plusieurs syntaxes pour créer un lien vers un titre :

- utiliser des ancres et des liens HTML standards avec la balise `<A>`, en utilisant un attribut `id`
  ou `name` pour identifier les ancres.
- utiliser des cibles automatiques Markdown vers les titres, passés en minuscules, débarrassés
  des caractères non alphanumériques et où les espaces sont remplacés par des traits d'union.
- utiliser des cibles `{:` dans les titres : ce style n'est pas reconnu par tous les éditeurs
  et visualiseurs Markdown et peut ne pas fonctionner très bien.

Le style d'ancres HTML le plus ancien `<A name="ancre"></A>` ou le plus moderne `<A id="ancre"></A>` ainsi
que les liens HTML `<A href="fichier#ancre"></A>` peuvent être utilisés dans les sources MLMD et fonctionneront
comme dans n'importe quel fichier Markdown ou HTML. Les liens Markdown automatiques `[](#titre)` fonctionnent
également comme dans les fichiers Markdown normaux : MLMD ne modifiera pas ces ancres et liens lors de la
génération. Toutefois ces liens demandent une modification du texte du titre qui doit être débarrassé des
espaces et des caractères non alphanumériques.

Par convention on évite généralement d'utiliser du HTML directement dans des fichiers Markdown afin de
permettre la génération vers d'autres formats, toutefois ce n'est pas interdit et les liens HTML sont plus fiables
que les liens Markdown, ces derniers n'étant pas toujours bien reconnus ou gérés par les logiciels d'affichage
ou d'édition Markdown

Ceci étant, MLMD peut générer un sommaire avec la directive `.toc` et va y insérer des liens vers les
titres des fichiers placés dans le sommaire. Pour respecter les conventions choisies, MLMD propose le choix entre
différents styles de liens et d'ancres dans les sommaires et les titres, à l'aide du paramètre `-out`.

| Paramètre      | Ancre des titres      | Liens sommaire             |
|---------------------|-----------------------|----------------------------|
| `-out htmlold`      | `<A name="target">`   | `<A href="file#target">`   |
| `-out html`         | `<A id="target">`     | `<A href="file#target">`   |
| `-out md`           | `<A id="target">`     | `[](file#target)`          |
| `-out mdpure`       | `{#id}`               | `[](file#target)`          |

MLMD affecte un identifiant globalement unique à chaque titre de chaque fichiers.

Il n'y a pas de meilleure méthode, chacune est appropriée à un contexte. Pour essayer un mode il
suffit de passer MLMD sur les fichiers en changeant le paramètre `-out` et de vérifier si le résultat
correspond à ce qui était attendu.

### II-5.1) Ancres HTML nommées : `htmlold`<A id="a14"></A>

Ce mode utilise des ancres de style ancien `<a name="id">` et des liens classiques `<a href>`. Il est
très approprié dans un contexte HTML standard, pour une documentation ou un système existants afin de maintenir
une excellente compatibilité.

### II-5.2) Ancres HTML identifiées : `html`<A id="a15"></A>

Les standards récents ont remplacé l'attribut `name` des ancres HTML `<A>` par l'attribut ìd`,
qui a l'avantage d'être automatiquement connu de Javascript. Dans ce mode, MLMD utilise `id` dans les
ancres. Il est particulièrement adapté pour une documentation HTML dans un environnement moderne
dynamique ou scripté.

### II-5.3) Ancres Markdown : `md`<A id="a16"></A>

Ce mode hybride utilise des ancres HTML avec un attribut `id` mais des liens Markdown `[]()`
dans le sommaire. Il est approprié aux documentations Github ou de logiciel et fonctionne
parfaitement dans différentes situations où le HTML est autorisé.

### II-5.4) Markdown pur : `mdpure`<A id="a17"></A>

Ce mode génère des ancres Markdown `{#}` dans les titres et des liens Markdown `[]()` dans
les sommaires et n'emploie aucune construction HTML. Il est très adapté aux contextes Markdown purs
ou lorsque la conformité des fichiers aux standards Markdown est vérifiée automatiquement. Toutefois
les ancres Markdown ne fonctionnent pas dans tous les processeurs Markdown (éditeurs ou browsers).
En cas de problème, le mode hybride `md` peut se révéler un meilleur choix.

### II-5.5) A propos des titres non uniques<A id="a18"></A>

En raison des liens automatiques vers les titres, par convention les fichiers Markdown ne doivent
généralement pas utiliser un même texte de titre plusieurs fois. Toutefois, excepté un warning des outils
de vérification Markdown (linting) de tels titres ne posent pas de réel problèmes à MLMD s'ils ne sont pas
explicitement visés par des liens automatiques Markdown. Pour ses propres liens de sommaire, MLMD alloue
un identifiant et une ancre unique à chacun des titres présents dans l'ensemble des fichiers sources
même si certains sont identiques. Ces identifiants ne sont toutefois pas connus avant la génération, aussi
le rédacteur ne peut pas les utiliser dans les fichiers sources de façon simple.

To solve this and use the unique MLMD headings anchors, the best way is to use explicit anchors in the
identical headings so they can be referenced in manual links in the source files. Either Markdown or HTML syntax
can be used. MLMD will forward these anchors and links into the generated files and won't mess with them, provided
they don't use the `a<integer>` format used by MLMD. (E.g. `a12`.). MLMD will add its own anchors and links which
won't interfere with the text body anchors and links.Pour répondre au besoin d'ancre unique dans des titres identiques, le meilleur moyen est d'utiliser des
ancres explicites dans ces titres afin de pouvoir les référencer dans des liens dans le corps du texte des
fichiers sources. Les syntaxes HTML ou MD peuvent être utilisées, MLMD reproduira simplement ces ancres et liens
dans les fichiers générés sans interférer avec ses propres ancres et liens de titres, à condition que les ancres
explicites n'utilisent pas le format `a<nombre>` de MLMD

Another solution is to process files once so each heading anchor can be checked in the generated files and
then used in the source files manual links, but this assume headings won't change in future source files
updates and is hazardous.Une autre solution est de générer les fichiers une première fois avec MLMD afin de révéler
les ancres uniques des titres dans les fichiers générés pour pouvoir les référencer dans les liens des
fichiers sources. Toutefois ceci suppose que les titres ne changeront pas lors de futures mises à jour
des documents sources et est déconseillé.

## II-6) Numérotation des titres : `-numbering`<A id="a19"></A>

Le paramètre `-numbering` indique un schéma de numérotation pour les différents niveaux
de titres rencontrés dans tous les fichiers sources et dans les sommaires. Par exemple un titre de niveau
3 peut être numéroté `A.2-5) Utiliser les objets`. La numérotation peut être choisie de deux façons :

- globalement pour tous les fichiers avec l'argument `-numbering`
- globalement dans le fichier principal avec la directive `.numbering`
- fichier par fichier avec la directive `.numbering` dans les fichiers sources

L'argument de la ligne de commande a priorité et supplantera les directives `.numbering``
dans les fichiers source. Ce qui suit traite de cet argument, la syntaxe de ses paramètres est identique
pour la directive qui sera abordée plus loin.

### II-6.1) Syntaxe<A id="a20"></A>

Le paramètre contient un nombre quelconque de définitions de niveaux séparées par une virgule :

```code
-numbering [<niveau>]:[<préfixe>]:<symbole>[<séparateur>][,...]]
```

Voici une description des parties de la définition de niveau

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

### II-6.2) Exemple<A id="a21"></A>

Voici comment numéroter les titres de niveau 1 avec les lettres 'A', 'B' etc, suivies
d'un tiret '-' puis d'un nombre suivi d'un point pour les titres de niveau 2 et d'un nombre
pour les titres de niveau 3 :

```code
-numbering 1:Chapitre :A-,2::1.,3::1
```

- Les titres de niveau supérieur à 4 ne seront pas numérotés et n'apparaîtront
  pas dans les sommaires si la directive .toc ne les mentionne pas. S'ils
  apparaissent dans la directive, ils seront préfixés par un tiret `-`.
- Le titre de niveau 1 sera préfixé et apparaîtra comme `Chapitre A)`.
- Les titres de niveau 2 seront numérotés `A-1)`, `A-2)` etc. Le préfixe
  du niveau 1 ne s'applique pas à partir du 2.
- Les titres de niveau 3 seront numérotés `A-1.1` puis `A-1.2`, `A-1.3` etc.

Seul le niveau 1 peut bénéficier d'un préfixe. Bien que l'on puisse indiquer
des préfixes d'autres niveaux dans le schéma de numérotation, ils n'auront aucun effet.
