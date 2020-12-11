# Output formats and numbering

## Output modes

- html      : html `<A>` anchors ("id") before headings, html `<A href>` links in TOC, `text` or `<numbered> text` in headings
- htmlold   : html `<A>` anchors ("name") before headings, html `<A href>` links in TOC, `text` or `<numbered> text` in headings
- md        : html `<A>` anchors ("id") before headings, MD `[](#)` links in TOC, `text` or `<numbered> text` in headings
- mdpure    : no anchors, MD `[](#)` *automatic links* in TOC, `text` or `1. text` in headings

## Numbering

Level 1 headings: numbering can specify a prefix which would be added to level 1 eading only, like 'Chapter' or 'Part' and a specific
terminator to separate the prefix + numbering from the text.

Levels 2 and above headings only feature numbering, which is always terminated by `)` before the text.

### Definitions syntaxes

Level 1 def:        `[<prefix>]:<symbol>:<separator>`
Level 2-9 def:      `:<symbol>:<separator>`
Numbering syntax:   `<level:><def>[,...]`

Symbols can be:

- `A` to `Z`: uppercase starting letter
- `a` to `z`: lowercase starting letter
- `&I`: uppercase Roman numbers (I, II, III etc), always start at 'I'
- `&i`: lowercase Roman numbers (i, ii, iii etc), always start at 'i'
- `1` to `9`: starting number

### Level 1 example

```code
    .numbering 1:.((Chapter .)).fr((Chapitre .)):X-
```

Will generate in 'en' file:

```code
    # Chapter I - *file title*
```

And in 'fr' file:

```code
    # Chapitre I - *titre fichier*
```

Level 2 to 4 example:

```code
    .numbering 2::A-,3::1,4::1
```

Will generate in files:

```code
    ## A) First part

    ### A-1) First sub-part of A

    #### A-1.1) First sub sub-part

    #### A-1.2) Second sub sub-part

    ## B) Second part

    ### B-1) First sub-part of B
```

| ***Element***                 | html        | htmlold     | md          | html+num    | htmlold+num | md+num      | mdpure      |
:-------------------------------|-------------|-------------|-------------|-------------|-------------|-------------|-------------|
***Heading Anchor:***           |             |             |             |             |             |             |             |
   `<A name="id"></A><br>`      |      -      |      o      |      -      |      -      |      o      |      -      |      -      |
   `<A id="id"></A><br>`        |      o      |      -      |      -      |      o      |      -      |      -      |      -      |
   `{#{id}}`                    |      -      |      -      |      o      |      -      |      -      |      o      |      o      |
***Hash Prefix***               |             |             |             |             |             |             |             |
   `# * level`                  |      o      |      o      |      o      |      o      |      o      |      o      |      o      |
***TOC heading space:***        |             |             |             |             |             |             |             |
   `4 x &nbsp; * (level-1)`     |      o      |      o      |      -      |      o      |      o      |      -      |      -      |
   `2 x <space> * (level-1)`    |      -      |      -      |      o      |      -      |      -      |      o      |      -      |
   `3 x <space> * (level-1)`    |      -      |      -      |      -      |      -      |      -      |      -      |      o      |
***TOC link to heading:***      |             |             |             |             |             |             |             |
   `<A href="file#id">text</A>` |      o      |      o      |      -      |      o      |      o      |      -      |      -      |
   `[text](#id)`                |      -      |      -      |      o      |      -      |      -      |      o      |      -      |
   `[text](#autoid)`            |      -      |      -      |      -      |      -      |      -      |      -      |      o      |
***TOC heading title:***        |             |             |             |             |             |             |             |
   `<num>) TOC Link`            |      -      |      -      |      -      |      o      |      o      |      -      |      -      |
   `- <num>) TOC Link`          |      -      |      -      |      -      |      -      |      -      |      o      |      -      |
   `- TOC Link`                 |      o      |      o      |      o      |      -      |      -      |      -      |      -      |
   `1. TOC Link`                |      -      |      -      |      -      |      -      |      -      |      -      |      o      |
***Heading title:***            |             |             |             |             |             |             |             |
   `<hash> <num>) text<anchor>` |      -      |      -      |      -      |      o      |      o      |      o      |      -      |
   `<hash> text<anchor>`        |      o      |      o      |      o      |      -      |      -      |      -      |      -      |
   `<hash> 1. text`             |      -      |      -      |      -      |      -      |      -      |      -      |      o      |

## Implementation

In all headings / links / anchors, a unique id numbered from "1" is available for each heading and is unique above all input files.

The unique `id` is not used in the `mdpure` mode. It is designed as `{id}` in all other modes.

The `{numbering}` is the sequence of numberings for a level. A level starts with the given `symbol` of its numbering definition, incremented for each following heading of the same level. Going down a level starts a new number with the new level starting symbol which is concatenated after the previous level separator. Going up one or more levels will continue with the next number of the destination level.

When more than one numbering schemes are on the way to the heading level, numberings are concatenated like in `A-1.2)`. The last level is always followed by `)` and a space even if it features a separator in its scheme definition.

TOC lines are only written if the corresponding heading is a level between start and end levels of the TOC. Other levels are ignored. A level between start and end can have a numbering scheme or not.

### html / html + num

- {anchor}:
    htmlold: `<A name="a{id}"></A><br>`
    html:    `<A id="a{id}"></A><br>`
- {heading}:
    `{anchor}{numbering}) text`     if the level has a numbering definition
    `{anchor}text`                  if the level has no definition
- {spacing}:
    `&nbsp` \* 4 \* (level - 1)
- {TOC text}:
    `{numbering}) text`             if the level has a numbering definition
    `- text`                        if the level has no definition
- {TOC line}:
    `{spacing}<a href="file#a{id}">{TOC text}</A>`

{heading} examples:

### md / md + num

- {anchor}:
    `{#a{id}}`                      extern `{` and `}` are litterals, e.g. `{#a12}`
- {heading}:
    `{anchor}{numbering}) text`     if the level has a numbering definition
    `{anchor}text`                  if the level has no definition
- {spacing}:
    `' '` \* 2 \* (level - 1)
- {TOC text}:
    `{numbering}) text`             if the level has a numbering definition
    `- text`                        if the level has no definition
- {TOC link}:
    `{spacing} - [{TOC text}](file#a{id})`
