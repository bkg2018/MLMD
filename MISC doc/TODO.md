# MLMD todo list

## TODO at 2020-12-11

- add pictures to documentation
- review full code coverage
- rework and fullfil unit tests
- polish comments in code
- make a node.js server version with real-time generation for one language
- try to generate progressively on empty lines instead of all in memory (previous
  tries were failures but lot of debug has been done since)
- drive tests through CommonMark test suite

## EVOLUTIONS at 2020-11-14

### localized pictures

Principle: a directive declares a directory where source pictures will be found, either under
localized versions in language subdirectories, either in common versions.

Another directive allows placement of pictures. They will be searched for localized or common  versions depending on the first declaration directive.

.pictures_root((<path>.))

Declares `path` as the root directory where to look for source pictures.

Localized pictures in a `code` language can be put in `code` subdirectories, where they will be found in the generated files. The name of each localized directory must be the same `code` as the one declared in the `.languages` directive.

Root and localized directories and pictures will be copied in the generated files directories.

Example: in source files, a directory named `pictures` is holding common pictures, as well as english localized pictures in the `pictures/en` and french localized pictures in `pictures/fr`. This will work only if the source files have a `.languages` directory with declaration for `fr` and `en` language codes.

.picture((<filename.ext>.))

Includes a source picture with the given name. The file will first be searched in the localized `code` subdirectory under the root directory declared by `.pictures_root` and if it is not found there, it will be searched in the root directory.

Example: in the main source MLMD file, `.languages fr, en` declares two languages with `fr` and `en`codes. In the main source file also, `.pictures_root((images.))` declares `/images` under the source files as the root directory for pictures. In a source MLMD file, `.picture((mainmenu.jpg.))` includes a picture. For the french `fr` generated file, it will include `images/fr/mainmenu.jpg` and for the `en` generated file, it will include `images/en/mainmenu.jpg`. If neither can be found, MLMD will look for a file in the root pictures directory, i.e. `images/mainmenu.jpg`. If this file cannot be found, a warning and error message will be displayed.

- smart handling for multiline block structures: lists, quotes so
  translation can be put line by line, currently doesn't work well
- copy of dependencies (images and other files) in output directory
- .. in relative filenames for {main} expansion while in subfiles
- directive to control the starting anchor ID in each file (similar
  to .topnumber) so included files can be generated separately
- rework variable: {filename} instead of useless {file}
- add tool variables: unique guid, url templates ...
- ~~add? .include(( directive and an opened-files stack in Filer instead of single input file~~


## BUG

Attention le texte en début de paragraphe ne va pas dans les fichiers d'une langue autre que default si des séquences .<code>((  sont présentes dans le paragraphe : il faut utiliser .all(( si on veut que toutes les
langues reçoivent ce texte. C'est particulièrement évident dans les séquences HTML qu'on place dans le mlmd source, par exemple :

    <img src="Pictures/045.jpg" alt=.(("Board front".)).fr(("Avant carte".)) style="zoom:50%;" />

Ici le but est que le préfixe avec le tag <img> soit placé dans toutes les langues mais il ne sera placé
que dans la langue par défaut, pour avoir l'effet souhaité il faut utiliser la syntaxe suivante :

    .all((<img src="Pictures/045.jpg" alt=.)).(("Board front".)).fr(("Avant carte".)).all(( style="zoom:50%;" />.))


~~Ne fonctionne pas :~~

~~    Reverse the board to see the **front** side, with the power button opening now appearing on the bottom right. ~~
~~    .fr((Tournez la carte pour voir l'**avant**, le trou du bouton power étant maintenant en bas à droite..))~~

~~    <img src="Pictures/045.jpg" alt=.(("Board front".)).fr(("Avant carte".)) style="zoom:50%;" />~~

~~Dans le .fr, le img src est tronqué et le texte français commence à "Avant carte".~~

~~Fonctionne :~~

~~    Reverse the board to see the **front** side, with the power button opening now appearing on the bottom right. ~~
~~    .fr((Tournez la carte pour voir l'**avant**, le trou du bouton power étant maintenant en bas à droite..))~~

~~    .((<img src="Pictures/045.jpg" alt="Board front" style="zoom:50%;" />.))~~
~~    .fr((<img src="Pictures/045.jpg" alt="Avant carte" style="zoom:50%;" />.))~~
