Basic example with a main title

.languages en,fr

# .((Basic example.)).fr((Exemple basique.))

.((This is the default text which will be in all the languages except the '{filename}.fr.md' file..)).fr((Ceci est le texte qui ira dans le fichier Français `{filename}.fr.md`..))

This text is outside any language directive and will go in each languages files.


---
Pure MD TOC:

1. Heading level N
   1. Heading level N+1
     - Heading level > max
2. Heading level N

Implementation:

- space \* 2 * level, '1. ', heading text
- space \* 2 * level, '- ', heading text if level > max

---

Schemed numbering MD:

- A) Heading level N
  - A.1) Heading level N+1
       - Heading level > max
- 2) Heading level N

Implementation:

- level <= max : space \* 2 \* level, '- ', numbering for level, ' ) ', heading text
- lvel > max :   space \* 2 \* max, space \* 4 \* (level - max), ' ', heading text
- space before ')' if last symbol is numeric
- numbering = <symbol><separator> using all levels <= level N

---
Schemed numbering HTML:

A) Heading level N<br>
&nbsp;&nbsp;&nbsp;&nbsp;A-1) Heading level N+1<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Heading level > max<br>
B) Heading level N

Implementation:

- `&nbsp;` \* 4 \* level, number for level, ') ', heading text
- `&nbsp;` \* 4 \* level, '- ', heading text if elvel > max
- number = <symbol><separator> using all levels <= level N
- number of `&nbsp;` can be anything
