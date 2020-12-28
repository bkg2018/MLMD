# III) Writing source files<A id="a22"></A>

The file sources must be named with a `.base.md` or `.mlmd` extension. They are UTF-8
text files with Linux/macOS or Windows end of lines. MLMD is UTF-8 compliant so macOS
and Windows encoding could have side effect, as any character between codes 128 and 255
would be invalid UTF-8. The source files must use UTF-8 for any characters other than ASCII.

The user has total control over which languages he/she wants to put in the sources.
Each language must be declared with one user chosen code. Using ISO standard codes
like `en-US` or `fr-FR` allow to easily link standard Web APIs for nation flags or
other international content, but more simple codes like `en` or `fr` are faster and
easier to write and can be associated to an optional ISO code. Both the language code and
the associated ISO code can be referenced in source text using variables which are
addressed later in this documentation. Look for `ISO 639` standard on the Web to find out
which code would be appropriate for your needs, as multiple standard versions exist.

See [`.language` directive](#declaring-languages-languages-directive) for the syntax
of languages declaration.

The following table summarize MLMD directives and source text effects in generated files. 

| Directive / text  | Effect in generated files |
|-------------------|---------------------------|
| `.languages`      | <ul><li>declares language codes: en, fr etc for file extension `.en.md`, `.fr.md` etc</li><li>may associate ISO codes: en_US …</li><li>may declare a main language with file extension `.md` alone</li></ul>|
| `.numbering`      | <ul><li>sets a numbering scheme for headings</li><li>equivalent to `-numbering` command line parameter</li><li>may set a specific scheme for current file</li></ul>|
| `.topnumber`      | <ul><li>sets the level 1 # heading number for this file in the global numbering scheme</li><li>set to 0 to disable level 1 heading numbering in current file</li></ul>|
| `.include`        | <ul><li>add a source file to generation and global numbering scheme</li></ul>|
| `# title`         | <ul><li>level 1 heading, used as Markdown file title</li><li>may include language specific text parts</li></ul>|
| `text …`          | <ul><li>default text when no language specific text exists for current part</li><li>easy use for original text before translation</li></ul>|
| `.fr((text.))`    | <ul><li>language specific text for `fr` code (french)</li><li>the language code must have been declared in `.languages` directive</li><li>`.fr((` opens language and `.))` closes it</li><li>can be followed by other language parts and preceded by default text</li></ul>|
| `.all(text.))`    | <ul><li>text will unconditionally go in all language files</li></ul>|
| `.!((text.))`     | <ul><li>text is ignored and won’t go in any generated file</li></ul>|  

## III-1) Source beginning<A id="a23"></A>

MLMD will not output anything into any file until it first meet a `.languages` directives
setting the output language codes. This is a restriction over all the input files present
in the input root directory and in subdirectories. The best practice is to put the `.languages`
directive in the first lines of the main input file. If the `.languages` directive appears
late in its file, any text before it will be ignored so if the output files look strange
and miss large parts fo text, the first thing to check is the `.languages` directive position.

A best practice can be to put the same `.languages` directive at the start of all source files
so there is no ambiguous interpretation but it's not mandatory.

- Any text before the `.languages` directive is ignored and not sent to any output file.
- The `.languages` directive must be written at the beginning of a separate line with
  no other content than its own parameters.
- Having some content before the heading level 1 is not conforming to Markdown conventions,
  but MLMD will write any text before the heading level 1 into all generated files, provided
  it appears after the .languages directive.
- The optional `.numbering` and `.topnumber` directives can be placed between the `.languages`
  and the level 1 `#` heading. The `.numbering` is ignored if a `-numbering` parameter is
  given to the command line.

## III-2) Including source files<A id="a24"></A>

Any file set by the `-i` command line parameters or from scanning the starting directory
can add other files in the set of input files. File inclusion do not actually insert a file content
but rather include the file into the set of processed files.

This allows a main file to reference the various parts of documentation from separate files
and keep a clean and simple main file with a global table of contents.

You can see an example of this use in the MLMD main documentation file in `docsource/MLMD.mlmd`.

### III-2.1) Include directive<A id="a25"></A>

The `.include` directive is followed by a relative file name which must be accessible
from the main file root directory. The file is not necessarily relative to the file
where the directive lies. It is written after the directive with no special delimiter
or quote characters around it and it ends the line.

MLMD documentation is organized as a main `README.mlmd` file with a main title and
a global Table of Contents, which includes 5 other files containing the different parts
of documentation. This maintains a clean and simple main file from which the reader can jump
to any part of documentation using the global TOC:

```code
  .include docs/1-Installation.mlmd
  .include docs/2-Using.mlmd
  ...
```

In this extract from the main `README.mlmd` file, the included files are in a `docs`
folder which lies at the same level as the `README.mlmd` file itself. The headings
in these files can be included in a global toc in the main file:

```code
  .toc level=1-3
```

This global Table Of Content will features headings level 1 to 3 from each processed file
including the one declared in `.include` directives even if only `README.mlmd` is given in
a `-i` command line parameter.

### III-2.2) Numbering main and included files<A id="a26"></A>

To control numbering of the main and included files the `.topnumber` directive allow
setting of each file level 1 heading. A good way of using it is to put .`topnumber 0`
in the main file to cancel numbering of its file title, then include the other files
and put `.topnumber` directives with numbers 1 and above to make sure te files will
appear with the right number in a global TOC.

The MLMD documentation uses this scheme:

- the main `README.mlmd` uses `.topnumber 0` to neutralize its level 1 heading numbering,
  and use `.include docs/1-Installation.mlmd` to include one file. The `#` heading is followed 
  by an introduction and then a `.toc` directive allowing levels 1 to 3 of all headings:

```code
  .numbering 1::&I:-,2::1:.,3::1
  .topnumber 0
  .include docs/1-Installation.mlmd
  .include docs/2-Using.mlmd
  ...
  .toc title=Table Of Contents.fr((Sommaire.)) level=1-3
```

- the included files use their own `.topnumber` value, which determine their order in the
  global TOC:

```code
  .topnumber 1
  ...
```

Refer to MLMD own documentation for a complete example of MLMD `.include` and `.topnumber` use.

## III-3) Headings<A id="a27"></A>

MLMD requires `#` prefixed headings and doesn't recognize the alternate syntax for
level 1 and 2 headings, which is available by adding `==` or `--` on the line
following the heading. The `==`and `--` lines can be used but will not be sufficient
for a heading to be recognized by MLMD.

```code
# This heading will be found by MLMD
This one won't be found because it doesn't have a # prefix
==========================================================
## This one will be found by its ## prefix
------------------------------------------
```

The `#` prefix must be followed by at least one space. Closing `#` have no particular effect.
Markdown conventions allow an empty line after a heading, MLMD doesn't change this but allow
more than one empty line, although it will only generate one in final Markdowns. Par convention dans un fichier Markdown on peut faire suivre
les titres d'une ligne vide, MLMD accepte même plusieurs lignes vides mais n'en écrira qu'une
dans les fichiers générés.

## III-4) End-of-Lines and End-of-Paragraphs<A id="a28"></A>

By default, except for headings and one-line directives, MLMD sources paragraphs are recognized
by MLMD as default text paragraphs which goes in all the language specific generated files except
those with corresponding language-specific parts. End of lines will be reproduced in generated
files, and empty lines will also make their way to generated files.

A language specific section can follow a corresponding default text paragraph if it is separated
by not more than one end of line, meaning it can follow the default text on the same line or 
starting on the next line. In that special case, the end of line separating default text from the
language start marker will be ignored by MLMD. This feature allows a clean separation of default
text paragraphs and language specific matching paragraphs. In practice, MLMD ignores a single end
of line when it only separates two languages parts. Doubled end of lines are always considered
as text for the current language and will appear as such in generated files. This allow a clean
organisation of translated text while conforming to Markdown.

Notice: the `.language((` and `.))` markers for language parts used in following examples will be
detailed later, they start or end a language specific part.

```code
This will be default text going in all languages files by default.
.fr((This will only go in french language file, which will not feature previous default text..))
This will be default text for all files except french file..fr((This will go into the french
file in place of previous default text..))
```

Normal Markdown conventions generally assume that text lines should not be larger than about
80 characters but MLMD sources do not enforce this limit. All text for all languages of a given
paragraph can be typed on a single line or not, and use any line length.

Here is an example:

```code
.((default text.))
.fr((texte français.))
.en((english text.))
Some other text....fr((Autre texte....))
```

MLMD interprets the three lines block this way:

1. The block starts line 1 by setting default text which will go in all generated file except those
   for which a language specific section is found.
2. The end-of-line at line 1 is ignored because it is immediately followed by another directive.
3. Line 2 will put text only in the FR file, which will ignore the default text.
4. The end-of-line at line 2 is ignored because it is immediately followed by another directive.
5. Line 3 will put text only in the EN file, which will ignore the default text.
6. The double end-of-line after line 3 ends the paragraph and generates text in relevant files.

Because end-of-lines are ignored when they only separate directives, the following line is
identical to the previous example and both will generate the same text in the same files:

```code
.((default text.)).fr((french text.)).en((english text.))
Some other text....fr((Autre texte....))
```

As this last example shows, both styles can be chosen for source files writing with an
identical effect. Generally, large sections of text for each language can be kept as paragraphs
separated by single ends of line while little text parts can be kept on a same line block.

### III-4.1) Notes<A id="a29"></A>

Although Markdown syntax limits lines to little more than 80 characters, Markdown viewers and
Web Browsers generally do not bother about this limit and will display the text correctly. Similarly,
modern text editors will make the text fit into the displayed width even if there is no end of line.
Most often they feature a 'soft-wrapping' setting or viewing option in menus to put artificial
end-of-lines which aren't actually in the file. For example, this option is called *Toggle Word Wrap* in
Visual Code and is accessible in the *View* menu:
![](https://github.com/bkg2018/MLMD/blob/main/docs/Images/word_wrap_vscode.png).

Although this is not mandatory, it is best to be consistent in the style of opening and closing
directives relatively to their text. A file should either use separate lines around the text for both
the opening and the closing directives, either put them on the same line around the text but avoid mixing
both techniques on large parts of text, or it would be unclear where the actual end of lines would be.

- Separated lines:

```code
  .fr((
  Some french text.
  .))
```

- Same line:

```code
  .fr((Some french text..))
```

## III-5) Multi-line blocks (lists, quotes, tables)<A id="a30"></A>

Because MLMD handles end of lines between language parts and default texte in a special way, some multiple
lines structures in markdown can not currently be translated line by line but rather block by block.

Here's an unordered list example:

```code
- first line
- second line
```

In Markdown, this will put two lines of text with a bullet prefix sign. A line by line translation
in MLMD could be written this way:

```code
- first line.fr((- première ligne.))
- second line.fr((- deuxième ligne.))
```

However, this will lead to a correct default languages structure but a wrong french translated structure
where end of lines will have disappeared:

Default text:

```code
- first line
- second line
```

French text:

```code
- première ligne- deuxième ligne
```

This behavior is because the end of line between french text and previous default text is cancelled.
This will be addressed in a future version of MLMD but currently, all lines must be kept in separate
blocks like the following example shows:

```code
- first line
- second line
.fr((
- première ligne
- deuxième ligne
.))
```

The same principle applies to other multi lines blocks:

- ordered lists (`1. etc`)
- quote lines (starting with `>`)
- tables (using the `|` separators)

All parts of these blocks must be treated as consistent blocks and be translated as a whole.
The MLMD documentation contains numerous examples of such structures.

## III-6) Escaping text<A id="a31"></A>

Directives and variables can be neutralized in a text section by surrounding it with the opening
escape `.{` and the closing escape `.}` directives. The directives won't have effect on generated files,
and variables and other directives will be copied as-is without expansion or interpretation.

Example:

```code
The .{.)).} directive closes a language part.
```

In this example, the `.))` directive will be considered as simple text and not as a directive.

## III-7) Quoted text and code fences<A id="a32"></A>

MLMD roughly copies the parts of text which are surrounded by *back-ticks* (reversed quote),
*double quotes* and *code fences*. In these parts of text, MLMD doesn't interpret directives
and variables:

- ```: code fences surround code text in which directives and variables will not
  be interpreted.
- `"`: double quotes around text neutralize directives and variables, e.g. `".(("` will not close
  the current directive.
- `` ` ``: back-ticks around text also neutralize directives and variables, e.g. `.((`
  will not open default text part.
- Quoted and fenced text must be entirely put *inside* enclosing directives (default or language
  directives) as they cannot embed directives.
- Simple quotes `'` have *no neutralizing effect* and no specific surrounding function.
  MLMD has been designed this way because the simple quote character is used separately in a lot of
  languages for other uses than surrounding text.
- Escaping back-ticks: to use actual back-ticks `` ` `` without the special effect, they can be
  embedded in doubled back-ticks with spaces
  (see [Markdown syntax about escapes](https://daringfireball.net/projects/markdown/syntax#autoescape))
  and this whole sequence surrounded by MLMD escaping.

## III-8) Variables<A id="a33"></A>

MLMD recognizes a few *variables*. These variables can be put anywhere in headings, links or text in the 
sources and will take a language specific value in the generated files.

| Variable      | Replaced by                                   | Example in generated file          |
|---------------|-----------------------------------------------|------------------------------------|
| `{file}`      | Name of the currently generated file          | `file.en.md`                       |       
| `{filename}`  | Base name of the currently generated file     | `file`                             |
| `{main}`      | Name of main input file with no extension     | `README`                           |
| `{extension}` | Extension of the currently generated file     | `.en.md`                           |
| `{language}`  | Language code of the currently generated text | `en`                               |
| `{iso}`       | ISO code associated to language code          | `en_US`                            |

The `{main}` variable will be replaced by the generated main file path (from the `-main` script argument).
This allows to link to anchors in the main file, like a global table of content for example. All the
tables of contents generated by MLMD have an anchor named or identified as `toc`. The style of the anchor
depends on the output mode.

Each variable takes a value at generation time, except for `{main}` which is only converted to a value
if a `-main` argument has been passed to MLMD. If no `-main` file is defined, the text will stay as
`{main}` in the generated files.

## III-9) Default text<A id="a34"></A>

MLMD accepts default text in any part of the source: headings, table of contents title, normal text etc.
The default text is used by MLMD when no language directive has been used to specify the language specific
text.

When not bounded by opening and closing language directives, text is always considered as default text.
This feature is detailed in the directive `.default((` later.

## III-10) Avoiding ambiguities<A id="a35"></A>

To avoid undesirable effects with end of lines, unordered or numbered lists and indented text,
a practical structure can be used for both the default text blocks and the language specific blocks.
First the default text opening directive is used on a single line, followed by default text, then a
new line closes default text and open a language section followed by the language specific text,
then a new line closes this section and the structure can be reproduced as many times as needed
for each language.

As the following example shows, this structure is easy to read and avoid ambiguity.

```code
.((
    - Here is some default text with special feature (indented list element)
    - Here is another line with default text
.)).fr((
    - Voici du texte en français avec une particularité (élément de liste indenté)
    - Voici une autre ligne avec du texte en français
.))
```

Although the default opening and closing directives are in fact optional, this structuration
with explicit directives on separate lines is an easy way to make sure the generated text
will be as expected.

## III-11) Directives<A id="a36"></A>

Actions for generating the language specific files are set by *directives* in the sources. MLMD
directives always start with a dot `.` except for escape text markers - see previous details
about escaping text.

Directives are of two types:

1. Immediate Directives are followed by parameters and modify some of
   the MLMD settings or generate text.
2. Text Directives enclose text between an opening marker `((` and
   an ending marker `))` and apply some effect to it.

Here's a summary of the immediate directives:

- `.languages` declares the language and iso codes available in source files
- `.numbering` sets the numbering scheme for the file headings and TOC
- `.topnumber` sets the number for the level 1 heading in numbering scheme for current file
- `.toc` generates a Table Of Contents in current file from all the chosen headings levels
  and the numbering scheme

Here's a summary of text open/close directives:

- `.all((` starts a text section which will be put in all the language files
- `.default((` or `.((` starts a section which will be put in the language files for which no specific language section is available
- `.ignore((` or `.!((`  starts a text section which will not be put in any generated file.
- `.<code>((` starts a text section which will be put only in the generated file for language `<code>` which has been declared in the `.languages` directive.
- `.))` ends a section started by one of the `.((` directives and returns to the previous directive effect.
- `.{` starts an escaped text section (directives and variables are not interpreted or expanded)
- `.}` ends an escaped text section

Directives are not case sensitive: `.fr((` is the same as `.FR((`. Notice that escape text markers 
work as opening and closing directives around escaped text, but as they directly derive from Markdown syntax
the markers will appear in the generated files, whereas MLMD directives won't.

## III-12) Immediate vs enclosed effect<A id="a37"></A>

The `.languages`, `.numbering`, `.topnumber` and `.toc` directives have an *immediate effect*.
It implies they generally should be alone on an isolated line, and preferably at the beginning of
source files. (This is mandatory for `.languages`, because anything preceding it will be ignored by MLMD.)

The other enclosing directives start with an opening `.<directive>((` marker which *encloses text* until a
matching `.))` is met, or until another `((` directive is opened.

> Although this is not very useful, enclosing directives can be embedded: each `.<code>((` opening
will suspend any current opened directive effect, and the matching `.))` closing will resume it.

## III-13) Default directives values and effects<A id="a38"></A>

Details will follow but it must be mentioned that the script has some defaults and that directives
themselves also have defaults settings.

- Anything preceding the `.languages` directive is *ignored* and won't be written in generated files.
  See [Declaring languages](#declaring-languages-languages).
- Empty lines before the level 1 heading are ignored.
- After the `.languages` directive, MLMD acts as if a `.default((` directive had been met, so any
  text will go into all the languages files except language specific text even before the level 1 heading.
  Notice that text preceding level 1 heading is not Markdown compliant but MLMD will put it in files.
- The `.default((` or `.((` directive will only have effect on languages which do not have a defined
  content yet, any previous `.all` text will make `.default` useless. See []().
- The `.toc` directive has default values which generate an table of contents for local headings of
  levels 2 and 3 in the current file. See [TOC](#generating-table-of-content-toc).
- The table of contents generated in any file always has an implicit anchor named `toc` which can be
  used to link to it from any other file.
