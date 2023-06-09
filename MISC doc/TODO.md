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

Ne fonctionne pas :

    Reverse the board to see the **front** side, with the power button opening now appearing on the bottom right. 
    .fr((Tournez la carte pour voir l'**avant**, le trou du bouton power étant maintenant en bas à droite..))

    <img src="Pictures/045.jpg" alt=.(("Board front".)).fr(("Avant carte".)) style="zoom:50%;" />

Dans le .fr, le img src est tronqué et le texte français commence à "Avant carte".

Fonctionne :

    Reverse the board to see the **front** side, with the power button opening now appearing on the bottom right. 
    .fr((Tournez la carte pour voir l'**avant**, le trou du bouton power étant maintenant en bas à droite..))

    .((<img src="Pictures/045.jpg" alt="Board front" style="zoom:50%;" />.))
    .fr((<img src="Pictures/045.jpg" alt="Avant carte" style="zoom:50%;" />.))
