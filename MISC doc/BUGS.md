* pas de EOL à la fin de la ligne :

    .fr((français.)).en((english.))
    default text.fr((texte français..))

* les lignes ne sont pas trimmées il peut rester un espace en fin de ligne

* ~~variable {main} non expansée si pas de fichier main => devrait être le premier fichier~~

* ~~{file} ne gère pas bien l'extension .base.md~~

* numérotation : le préfixe 'chapter' n'apparait que sur le premier titre de niveau 1

* ~~dans README, tout va dans le fichier français sauf les titres ! Ca semble lié à la quantité, ou peut-être au réordonnancement avec array_values ?~~ c'était du à un .fr(( pas fermé, dépilage ajouté à la fin de process() avant output()

* ~~TOC: le préfixe ## du titre n'apparait pas dans le fichier FR mais apparait bien dans le fichier EN: problème de default language ?~~

TOC exemple level 1-3 actuel:
        # Chapter I) English File Title MLMD<A id="a1"></A>

        ## Default toc title<A id="toc"></A>

        - Chapter I) [English File Title MLMD](test.md#a1)
          - I-1) [Default title 2.1](test.md#a2)
              - I-1.1) [Title 3.1.1](test.md#a3)
          - I-2) [Title 2.2](test.md#a6)
        - II) [Secondary MLMD file](subdata/secondary.md#a7)
          - II-1) [Secondary title 1.1](subdata/secondary.md#a8)
          - II-2) [Secondary title 2.1](subdata/secondary.md#a9)
        - III) [Tertiary MLMD file](subdata/tertiary.md#a10)
          - III-1) [Tertiary title 1.1](subdata/tertiary.md#a11)
          - III-2) [Tertiary title 2.1](subdata/tertiary.md#a12)

Structure souhaitée :

    # English File Title MLMD<A id="a1"></A>

    ## Default toc title<A id="toc"></A>

    - [English File Title MLMD](test.md#a1)
    - 1) [Default title 1](test.md#a2)
        - 1.1) [Title 1.1](test.md#a3)
    - 2) [Title 2](test.md#a6)
  
    - Chapter I) [Secondary MLMD file](subdata/secondary.md#a7)
      - I-1) [Secondary title 1.1](subdata/secondary.md#a8)
      - I-2) [Secondary title 2.1](subdata/secondary.md#a9)
    - Chapter II) [Tertiary MLMD file](subdata/tertiary.md#a10)
      - II-1) [Tertiary title 1.1](subdata/tertiary.md#a11)
      - II-2) [Tertiary title 2.1](subdata/tertiary.md#a12)
  - 
* Revoir le schéma de numérotation :
  ~~* autoriser .topnumber 0 pour désactiver la numérotation niveau 1 dans un fichier~~
  * pas de préfixe ni numérotation niveau 1 sur fichier main ?
