# V) Correction des fichiers sources<A id="a67"></A>

Les directives MLMD et les paramètres de la ligne de commande forment une sorte
de langage de programmation avec lequel on rédige les fichiers sources, que l'on compile
ensuite en fichiers Markdown.

Comme avec tout langage de programmation, les fichiers sources comportent parfois
des *bugs*. MLMD fait son possible pour aider à localiser ces bugs mais il existe aussi des
techniques qui limitent les risques

Cette partie décrit les outils offerts par MLMD, les bugs possibles et les techniques pour
les éviter.

Mais tout d'abord, un conseil important

## V-1) NE CORRIGEZ PAS LES FICHIERS GENERES<A id="a68"></A>

Si vous corrigez des erreurs directement dans les fichiers en français, anglais, polonais, coréen
ou tout autre langue, vous retrouverez vos erreurs la prochaine fois que vous passerez MLMD sur vos sources.

Le bon endroit où corriger les erreurs est le fichier source.

Lisez soigneusement vos fichiers générés, notez les erreurs, puis corrigez les dans les
fichiers sources avant de régénérer les fichiers.

## V-2) Mode trace : `-trace`<A id="a69"></A>

Le paramètre `-trace` facultatif affiche une trace des lignes lues dans les fichiers sources.
Si les lignes générées ne sont pas celles espérées, la première chose à faire est de regarder cette
trace: MLMD doit affiche chaque ligne avec son numéro, sauf pour les portions de barrière de code (`.!`)
où seule la première ligne est affichée.

Il faut s'assurer que toutes les  lignes sont présentes et que les barrières de code sont
bien repérées : s'il manque une barrière fermante par exemple, la trace sautera toutes les lignes
suivant l'ouverture soit jusqu'à la prochaine barrière, soit jusqu'à la fin du fichier.

## V-3) Sections non closes<A id="a70"></A>

A la fin de  chaque fichier source, MLMD vérifie si toutes les sections de langue ont bien
été fermées et signale la ligne de début de celles qui ne le sont pas. Il est important de bien lire
les avertissements et de fermer les sections dont le `.))` a été oublié.

## V-4) Mauvaises indentations<A id="a71"></A>

MLMD reproduit les indentations situées *après* les directives d'ouverture de langue, aussi lorsque
l'indentation semble disparaître il faut vérifier si elle est correctement placée. L'exemple suivant :

```code
   .fr((Text.))
```

générera `text` sans indentation dans le fichier `.fr.md`. Pour conserver les espaces la ligne doit
être réécrite de cette manière :

```code
.fr((   Text.))
```

## V-5) Langue erronée<A id="a72"></A>

Lorsque le texte de la mauvaise langue se retrouve dans un fichier qui ne lui était pas destiné, 
cela signifie en général que la mauvaise directive d'ouverture a été utilisée ou qu'une section
n'a pas été refermée :

- il manque peut-être une parenthèse ouvrante : `.fr(` ne démarre *pas* une section en français
- un mauvais code a peut-être été utilisé : `.enn((` n'est pas une directive si le code attendu est `en`
- une directive de fermeture a peut-être été mal écrite : `.)` ne ferme pas la section en cours

En conclusion, lorsque quelque chose ne va pas concernant la langue utilisée, il faut
vérifier les directives d'ouverture et de fermeture autour du texte concerné.

## V-6) Texte par défaut mal placé<A id="a73"></A>

Le texte par défaut ne sera pas généré dans les fichiers attendus s'il apparaît
après les sections spécifiques aux langues plutôt qu'avant elles. Le texte par défaut doit
s'applique toujours aux sections de langue qui le suivent, et non à celles qui le précèdent.

 Voici un exemple de mauvais ordre :

`.fr((Texte en français.))Default text`

Avec cet ordre, le fichier français va recevoir `Texte en français`, puis le 
texte par défaut `Default text` sera envoyé dans tous les fichiers y compris le français.

Pour corriger cela il faut placer le texte par défaut en premier et les sections des
langues ensuite :

`Default text.fr((Texte en français.))`

Cette ligne corrigée aura l'effet désiré : le texte par défaut n'ira pas dans le fichier français.

## V-7) Listes et tableaux Markdown incohérents<A id="a74"></A>

Markdown permet de rédiger facilement des listes à l'aide des préfixes `*` et `-` en début de
ligne ainsi que des tableaux de lignes. Toutefois avec MLMD écrire chaque ligne de tableau ou élément
de liste avec toutes ses sections de langues peut avoir des effets indésirables en raison de l'interprétation
spéciale de la fin de ligne entre les directives fermantes et ouvrantes des sections. Une méthode plus
appropriée est de conserver les tables et listes entières dans des sections de langue séparées.

Dans l'exemple suivant on essaie de traduire chaque ligne une à une :

```code
.en((- first line in english.)).fr((Première ligne en français.))
.en((- second line in english.)).fr((Deuxième ligne en français.))
```

Le problème est que la fin de la première ligne est supprimée par MLMD parce qu'elle intervient
entre la fermeture de section française et l'ouverture de la suivante en anglais. Au lieu de cela
il faut rédiger les listes séparément chacune dans sa propre section :

```code
.en((
- first line in english
- second line in english
.)).fr((
- première ligne en français
- deuxième ligne en français
.))
```

Bien que cela ne paraisse pas aussi simple c'est une méthode beaucoup plus fiable et facile à contrôler,
avec laquelle MLMD générera toujours les bonnes sections dans les bons fichiers sans supprimer de fins de
lignes de manière apparemment intempestive.

Il faut souligner que cette suppression de fin de ligne est un compromis qui a été adopté dans
MLMD pour faciliter la séparation visuelle des sections de différentes langues.

## V-8) Mauvaise numérotation des titres<A id="a75"></A>

Lorsque la numérotation des titres ne correspond pas à celle attendue, voici quelques erreurs
possibles :

- une supposition erronée quant à l'ordre de traitement des fichiers sources qui modifie le numéro
  du titre de niveau 1 et, indirectement, le premier nombre de la numérotation de chaque titre
- il peut y avoir un bug dans la rédaction du schéma de numérotation, par exemple il manque un `:` ou `,`
- il peut y avoir plusieurs directives `.numbering` incompatibles dans les fichiers sources

Pour obtenir un bon résultat, les fichiers traités peuvent respecter les règles suivantes :

- utiliser le même schéma `.numbering` ou ne pas en utiliser et laisser le paramètre `-numbering` de la ligne 
de commande faire le travail
- utiliser `.topnumber` pour réserver leur numéro de titre de premier niveau ou le supprimer avec `.topnumber 0`

Les fichiers sont triés dans les sommaires et le traitement selon leur numéro `topnumber`, mais
en l'absence de celui-ci ils seront ordonnés en fonction de leur place dans leur répertoire, qui n'est
généralement pas celle attendue et pratiquement jamais celle affichée par les systèmes d'exploitation.
Les meilleurs résultats sont donc obtenus en utilisant `.topnumber` dans les fichiers sources.

## V-9) Disparition de point final<A id="a76"></A>

Si des points terminant des phrases disparaissent dans les fichiers générés, cela provient 
généralement d'une confusion avec le point qui commence la directive de fermeture d'une section. A la fin
de la dernière phrase d'une section, il faut doubler le point. Le premier point termine la phrase,
le second est celui de la directive de fermeture :

```code
.fr((Cette phrase ne se termine pas par un point.))
.fr((Cette phrase se termine par un point..))
```

## V-10) Orthographe<A id="a77"></A>

La plupart des éditeurs de texte possèdent des modules de correction orthographique
qui vérifient à la volée le texte au fur et à mesure de sa saisie Visual Code par exemple
propose une extension qui supporte de nombreuses langues.

Si possible, la vérification doit être effectuée sur les fichiers sources, toutefois cela peut
poser problème si plusieurs langues sont activées et proposent des orthographes différentes pour des
mots similaires, le module ne signalant alors pas d'erreur. Par exemple, le mot `example` dans
du texte en français ne sera pas signalé si le correcteur vérifie aussi la langue anglaise.

Si la relecture des fichiers générés révèle beaucoup d'erreurs, alors on peut activer la correction
avec juste la langue concernée sur le fichier généré, noter toutes les erreurs et les corriger dans
les fichiers sources avant de régénérer les fichiers avec MLMD.
