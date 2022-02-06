# MLMD - Multilingual Markdown Generator<A id="a1"></A>

<img src="https://flagcdn.com/256x192/gb.png" width="25" height="19"> : [README](README.md), [Documentation](docs/MLMD.md)<br />
<img src="https://flagcdn.com/256x192/fr.png" width="25" height="19"> : [README](README.fr.md), [Documentation](docs/MLMD.fr.md)<br />

MLMD generates one or more Markdown files for a set of declared languages from one or more
multilingual UTF-8 encoded source files, following directives in the files to distinguish each
language parts.

The country flags in this documentation are free for any use and furnished by the https://www.drapeauxdespays.fr site.

## How?<A id="a2"></A>

MLMD is a PHP script. It takes source files and some parameters, analyzes 
them and generates corresponding Markdown files for each declared language.

MLMD requires a PHP interpreter version 7 or more.

## What?<A id="a3"></A>

An MLMD source file is an UTF-8 encoded text file using the `.mlmd` extension.
The `.base.md` extension is also supported.

MLMD processes each source file for each declared language, and generates Markdown files
for each file and each language: *`file.mlmd`* will generate *`file.fr.md`*, *`file.en.md`*
etc.

Languages must be declared in at least one of the source which are preprocessed before actual
file generation starts. Parts of the text for each language are enclosed between opening and
closing directives, while other directives can generate tables of contents or heading numbering,
add other input files and do various other tasks.

![File generation and directives](docs/Images/FileGeneration.png)

## How fast?<A id="a4"></A>

Writing language parts is fast and easy. Default text, which is generally the original author's native
language, doesn't need any specific directive and is written just like normal text, while language
specific parts are surrounded by simple open/close directive. For example starting english text
is done by writing `.en((`, followed by the english text which is eventually ended by `.))`. Empty lines,
code fences, tables, any text and Markdown features can be put between these two markers. Headings starting
with `#` can also include other language parts, or they can be put between markers like any text.

The following example show both ways of writing multilingual headings:

```code
# Example of a multilingual title.fr((Exemple de titre multilingue.))
.((# Default title.))
.fr((# Titre en français.))
```

As of processing speed, MLMD script generates the 2000 lines of its own documentation 
in about 2 seconds on an Intel i7 at 2 GHz, files being written on a SSD.

## Why?<A id="a5"></A>

I designed MLMD because I needed to write a technical documentation both in English and
French for a DIY electronics project. Looking on the Web I found a very efficient
[Python script from Hong-ryul Jong](https://github.com/ryul1206/multilingual-markdown).
However after using it a while I found I needed some more features and I wanted to design
a set of directives which would keep the text more readable and easier to type than HTML
comment tags. I kept most of basic Hong-ryul original ideas like ignored text and language declaration
but reworked the design so directives could be embedded into the text rather than using the
HTML comment syntax. I also designed various output modes to adjust for different HTML or pure
Markdown contexts.

MLMD lets the user write default text when no language specific translation is available, 
put sophisticated numbering schemes for heading levels in all or each file, generate global
or local table of contents, include other files, escape text, use a few variables to ease
language-independent linking to other files and many other tasks.

Special features let the writer separate language parts or stream them as he/she likes.

Writing MLMD files is almost as easy as writing Markdown files. MLMD is UTF-8 by nature and
will accept any language and characters sets in source files.

MLMD is a convenient way of keeping the documentation structure in source files while permitting
translation by different authors in a unique and shared place. And being text files, the MLMD
multilingual sources work very well with versioning systems like Git, allowing diff comparisons,
pull requests and files merging.

## Documentation<A id="a6"></A>

- [Full documentation](docs/MLMD.md) for MLMD can be found in the `docs` directory.

- The [source file for this README](README.mlmd) documentation is the `README.mlmd` file.

- The [full documentation source](docsource/MLMD.mlmd) is written in MLMD itself and can be found
in the `docsource` directory. It is a comprehensive example of MLMD source possible writing styles
and directives uses and shows how to include other source files in a documentation.

### Building full documentation<A id="a7"></A>

Building the MLMD documentation is done with the following command:

```code
php src/mlmd.php  -i docsource/MLMD.mlmd -out md -od docs
```

- The `-i` parameter tells MLMD where to find the input file, which in turn includes other MLMD source files.
- The `-out` parameter sets the mixed Markdown/HTML output mode for links and Table Of Contents
- The `-od` parameter gives a path where to write the generated documentation files. All paths given here are relative to
the directory from where the script is launched, but absolute paths can also be used.

### Building README<A id="a8"></A>

Building this README documentation you're currently reading is done with the following command:

```code
php src/mlmd.php -i README.mlmd -out md
```

## Enjoy!<A id="a9"></A>

I hope MLMD will help you to easily maintain multilingual documentations.

In these Covid days, please stay safe and take care of others.

Francis Piérot

August-December 2020<br />
To my father Serge, 1932-2020<br />
He taught me an engineer works hard at working less.

<img src="https://flagcdn.com/256x192/gb.png" width="25" height="19"> : [README](README.md), [Documentation](docs/MLMD.md)<br />
<img src="https://flagcdn.com/256x192/fr.png" width="25" height="19"> : [README](README.fr.md), [Documentation](docs/MLMD.fr.md)<br />
