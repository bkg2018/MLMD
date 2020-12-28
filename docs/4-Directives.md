# IV) Directives Reference<A id="a39"></A>

In this part, each directive will be explained in detail with syntax, use notes
and examples.

## IV-1) Declaring languages: `.languages`<A id="a40"></A>

The `.languages` directive declares the possible languages which can be found in the sources, assign them
a code and an optional associated ISO code, and optionally tells which code is the *main* language.

The *main* language has files generated without the language code suffix, e.g. `README.md` while other
languages will have the language code suffix, e.g. `README.fr.md`.

### IV-1.1) Syntax<A id="a41"></A>

The `.languages` directive lies alone on a line and is followed by the list of language codes to be
used in all source files, optionally associated to an ISO code. One code can be declared as the
main language.

```code
.languages <code>[=<iso>][,...] [main=<code>]
```

Each  `<code>` declares a language which can then be used with `.<code>((` directives to start text
sections for the `<code>` language.

The optional `main=<code>` parameter tells which language is the main language: files generated for
this main language will have an `.md` extension instead of a `.<code>.md` extension. As an example,
the `README.base.md` source file will generate a `README.md` for the main language and
`README.<code>.md` for other language codes. This is particularly useful with Git deposits which
require a `README.md` file at the deposit root.

### IV-1.2) Notices<A id="a42"></A>

- No file is generated before the `.languages` directive is met: any preceding text will be ignored.
- The directive has a global effect over all the source files so it can be put in the first processed
file. If there is any doubt about which file will be processed first, the directive can be put in all
the sources with no undesirable effect. The order can also be chosen with a `.topnumber` directive
in each source file.
- After the `.languages` directive, the generator will consider all text as default text
and send it to all languages files until a language opening directive changes this.

### IV-1.3) Example<A id="a43"></A>

```code
.languages en=en_US,fr main=en
```

Generated files will be named with a `.md` extension for the `en` language and with `.fr.md` for
the `fr` language.

## IV-2) Defining a numbering scheme: `.numbering`<A id="a44"></A>

The `.numbering` directive defines the numbering scheme for current file headings and TOC lines.
The syntax is identical to the `-numbering` command line argument.

> WARNING: the `-numbering` command line argument has global effect on all files, while the `.numbering`
directive only applies to the file where it appears.

### IV-2.1) Syntax<A id="a45"></A>

```code
.numbering [<level>]:[<prefix>]:<symbol>[<separator>][,...]]
```

Following are details about each definition part. These are identical as for the command line parameter.

- `<level>` is optional and is a digit between `1` and `9` representing the heading level (which is the number of '#' at
the heading beginning). By default, this defines the next level, starting with 1 and incrementing at each definition.
- `:` is a mandatory separator between parts of the definition, even for the empty ones.
- `prefix` is an optional prefix for the level numbering, e.g. '`Chapter `'. The prefix only appears in the level numbering
for the heading of this level, and will be omitted from inferior levels numbering.
- `<symbol>` is a mandatory symbol which can be an uppercase letter `A` to `Z`, a lowercase letter from `a` to `z`,
a digit from `1` to `9` or the special notation '&I' or '&i' for uppercase or lowercase Roman numbers. It sets the starting
value for the level numbering, except for Roman numbers which always start at `I`.
- `separator` is an optional symbol which will be concatenated to the numbering symbol before the next level numbering.
Conventional symbols can be `.`, `-`or `)`. Omitting this separator for the last level is identical to using `.`.
- `,` is a separator before the next level definition.

A level N always starts with the defined symbol, then all the following headings at the same level N will increment
this symbol until a heading with a level N-1 above will reset the current level N, and continue with the next number
in the setting for the above level N.

## IV-3) Numbering level 1 heading: `topnumber`<A id="a46"></A>

The `.topnumber` directive defines the starting number for the level 1 heading in the current file for the
numbering scheme set by `.numbering` or the `-numbering`command line parameter. This 
can be used to number successive source files in table of contents. Each file has only one level 1 heading.
If the `.topnumber` directive is used in files, they will be processed in the order defined by this
directive.

### IV-3.1) Syntax<A id="a47"></A>

```code
.topnumber <n>
```

The parameter `n` can be any integer number starting at 1. Each file should have a unique number, as using
identical numbers will have unpredictable effects.

## IV-4) Generating Table Of Content: `.toc`<A id="a48"></A>

The `.toc` directive generates a Table Of Contents using chosen header levels. The header levels are defined
by the number of `#` prefixes in Markdown syntax: `#` is header level 1 which is unique in a file, `##` is
level 2 etc.

By default, level 1 is ignored: it is considered as the file title, and levels 2 to 4 are put in TOC.
But level 1 can be used to build a global TOC for the headings from the full set of files. Such a global
TOC is generally placed in the main file from which all other files can be accessed.

The Table of Contents has one link for each accepted heading.

> The table will be put at the place of the `.toc` directive.
> The table has an automatic anchor named `toc` which can be used in links from other files.
> If a numbering scheme has been set with the `-numbering` script argument or
  `.numbering` directive, it will be used for the toc headings.
> The TOC title is written as a level 2 heading in the generated files.

### IV-4.1) Syntax<A id="a49"></A>

The `.toc` directive must be written alone on its line with its parameters. Most of the time, the TOC
lies after the file title and some introduction. A default TOC with no parameters will build a table
of contents for the current file with headings `##` to `###`. (Level 2 to 4 headings.)

```code
.TOC [level=[m][-][n]] [title=<title text>] [out=md|html]
```

#### IV-4.11) `level` parameter<A id="a50"></A>

This parameter sets the heading levels which will appear in the TOC.

Syntax for this parameter is `level=[m][-][n]`:

- If neither `m` nor `n` are given, the TOC will contain heading levels 2 to 4 (matching
  headings `##` to `###`).
- If `m` only is given, TOC titles will be level `m` headings only.
- If `m-` is given without `n`, TOC titles will be level `m` to level 9 headings.
- If `m-n` is given, TOC titles will be level `m` to level `n` headings.
- If `-n` is given, TOC titles will be level 1 to level `n` headings.

#### IV-4.12) `title` parameter<A id="a51"></A>

This parameter is followed by text which will be placed as a level 2 (`##`) heading right
before the table.

- The title text can use language, all, ignore and default directives just like any other text.
- If no title text is given, `Table Of Contents` will be used.
- Everything that follows `title=` is used as title text until either the end of the line,
  either the `level=`parameter.

### IV-4.2) Examples<A id="a52"></A>

```code
.TOC level=1-3 title=2,".fr((Table des matières.)).en((Table Of Contents))"
```

This directive generates a TOC using the headings `#` to `##` found in each file. The order
of files in the table will be either the one dictated by `.topnumber` directives in files,
either the 'natural' order of files in directories, which is generally the order they were written
to for the first time and is not easily controllable.

The table title will be `## Table Of Contents` by default in any language.

## IV-5) Generating for all languages: `.all((`<A id="a53"></A>

The `.all((` directive starts a section of text which will be put in each of the languages
files declared in the `.languages` directive.

This directive is ended or suspended by any of the following conditions:

- The `.))` directive which returns to previous state.
- The `.<code>((` directives which start a language specific section of text.
- The `.ignore((` directive which starts ignored text.
- The `.default((` or `.((` directive which starts the default value for a section of text.

By default, any text outside directives and appearing after the `.languages` directive
is generated as default text in all the languages files with no specific text as if it were
in a `.((` section.

### IV-5.1) Syntax<A id="a54"></A>

```code
.all((
```

### IV-5.2) Examples<A id="a55"></A>

Directives can always be alone on a line, surrounding the text they act on:

```code
.all((
text for all languages
.))
```

They can also be put inline within text:

```code
.en((text for 'en' language .all((text for all languages.)) rest of text for 'en' language.))
```

And they can be embedded within headings:

Remember that by default, text goes in all the language files with no specific section. This défault context
is resumed when no language specific section is active anymore, as it is at the end of the exemple above after the
last `.))` directive.

## IV-6) Default text: `.default((` or `.((`<A id="a56"></A>

The `.default((`  or `.((` directive starts a default text section which will be put in
the generated language files for which no specific language section is available after this
default section.

This directive is not generally needed as it is always active when no language specific
section is. It applies to all the upcoming text until either a closing or an opening
language directive is met.

Putting text in `.default((` is **not** the same as `.all((`:

- Text for all languages will unconditionally go in each generated file for each language.
- Default text will only go in files for which there is no language section following it.

The goal of the `.default((` directive is to prepare the original text and headings in a
common language like english, then add language specific sections on the fly while still having
the default text for languages which are not translated yet.

### IV-6.1) Syntax<A id="a57"></A>

```code
.default((
```

or:

```code
.((
```

### IV-6.2) Examples<A id="a58"></A>

An special use of default text is in headings, because the `#` is handled separately and
is automatically written for all languages by MLMD without the need to use directives, and the default
context is restored after this prefix:

```code
# .Main Title.fr((Titre principal.))
```

This will put `# Main Title` in all the generated files except the `.fr.md` file where the
generator will put `# Titre Principal`.

For text blocks, the default text can be put right before the language specific sections, or it
can be explicitly placed into opening default and closing directives to avoid ambiguity. Single
end of lines will be ignored when they only separate closing and opening directives with no text
in between, which permits a visual separation of blocks.

Here the default text is directly followed by a french translation and explicit default `.((`
directive is not needed:
```code
This is the default original text..fr((Ceci est la traduction en français..))
```

In the following example, the default and specific sections are explicit to avoid
any ambiguity:

```code
.((
This is the default original text.
.)).fr((
Ceci est la traduction en français.
.))
```

## IV-7) Ignoring text: `.ignore` or `.!((`<A id="a59"></A>

The `.ignore` directive starts an ignored section of text. Ignored text won't be put in
any generated file. It is useful for many tasks:

- comments about the source file or text sections
- TODO lists in source files
- work in progress text sections which are not ready for publishing yet

This directive is ended or suspended by:

- The `.))` directive which returns to previous state.
- The `.all((` directive which starts a section for all languages.
- The `.<code>((` directives which start a language specific section of text.
- The `.default((` or `.((` directive which starts the default value for a section of text.

### IV-7.1) Syntax<A id="a60"></A>

```code
.ignore((
```

### IV-7.2) Example<A id="a61"></A>

The directive can be applied to full blocks of text:

```code
.ignore((
text to ignore
.))
```

The directive can also appear in default or language specific text:

```code
Text to generate .ignore((text to ignore.)) following text to generate
# Title for all languages .ignore((ignore this.)) title following
```

## IV-8) Generating for languages: `.<code>((`<A id="a62"></A>

The `.<code>((` directive starts a section of text which will only be put in the generated
file for the language whose code `<code>` has been declared in the `.languages` directive.

This directive is ended or suspended by:

- The `.))` directive which returns to previous state.
- The `.all((` directive which starts a section for all languages.
- Another `.<code>((` directives which starts a language specific section of text.
- The `.default((` or `.((` directive which starts the default value for a section of text.
- The `.ignore` or `.!((` directive which starts ignored text.

Language sections must be closed by a matching `.))`. Although sections can be chained,
it is recommended to close a section before beginning an other one, else you'll have to
close all of them at the end of sections. See examples below for language chaining.

### IV-8.1) Syntax<A id="a63"></A>

```code
.<code>((
```

In this syntax, `<code>` is one of the codes declared after the `.languages` directive
at the source files start. The angle brackets `<` and `>` are only for notation and not part
of the code and should not be entered.

### IV-8.2) Examples<A id="a64"></A>

The directive can enclose text or headings:

```code
.en((
Text for English language only.
## Heading for English generated file
.))
```

It can also be put inline within text or headings:

```code
.fr((Texte pour le fichier en Français.)).en((text for the English file.))
# .fr((Titre en Français.)).en((English Title.))
```

Notice that the apparently ending '.' in titles is in fact the dot from the `.))` 
closing directive. This somewhat misleading visual effect can be avoided by using spaces:

```code
.fr((Un peu de texte en Français. .)).en((Some english text. .))
```

The spaces between directives are generally default text and will restore the default context,
which can have undesired effects as it would break the current default/specific text chain.
To put a space after some text it is best to put it inside the language blocks:

```code
.fr((Texte en Français suivi d'un espace .)).en((English text followed by a space .))
```

As mentioned above, language sections can be chained without closing them, but each one will
have to be closed eventually. The line below has the same effect as the previous example:

```code
.fr(( Texte en Français .en(( English text .)).))
```

Notice that there are two successive closing directives at the end of the line.

A opened section stays active until it is closed or until next directive is met. In the
next example, the closing on the first line ends the `.en` section, but the `.fr` stays active
and the following text will be generated in the `.fr.md` file. The example shows other
effects of opening and closing directives.

```code
This text has no directive and will go in all files.
.fr(( Texte en Français .en(( English text .))
This text will only go into the french file because the opening . fr (( directive has not
been closed yet. .))
Now this text is in the `all` section and go in all files.
# .fr(( Titre en Français .en(( English Title .))
This text will only go into the french file because its opening directive has not been closed yet.
```

## IV-9) Escaping text: `.{` and `.}`<A id="a65"></A>

Text can be 'escaped' by surrounding it with `.{` and `.}`.

In the escaped text, directives and variables are ignored and text is copied as-is in the
generated files.

In Markdown syntax, text can also be escaped by surrounding it with single or multiple
back-ticks `, code fences ``` or double quotes `"`. MLMD will respect these Markdown
escaping and forward the escaped text with its escape markers into generated files while
ignoring any variables and directives in it. The difference with MLMD escaping directives is that
these last directives `.{` and `.}` will not be written and only the escaped text will.

## IV-10) Examples<A id="a66"></A>

The `Examples` directory has a few `mlmd` and `base.md` examples sources.
