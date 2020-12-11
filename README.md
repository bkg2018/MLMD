# MLMD - Multilingual Markdown Generator<A id="a1"></A>

MLMD generates one or more Markdown files for a set of declared languages from one or more
multilingual source files UTF-8 encoded, using directives in the files to distinguish each
language parts.

## How?<A id="a2"></A>

MLMD is a PHP script. It takes source files and some parameters, analyzes 
them and generates corresponding Markdown files for each declared language.

MLMD requires a PHP interpreter version 7 or more.

## What?<A id="a3"></A>

An MLMD source file is a text file encoded with UTF-8 and using the `.mlmd` extension.
The `.base.md` extension is also supported.

MLMD process the source files for each declared language, and generated Markdown files
for each file and each language: *`file.mlmd`* will generate *`file.fr.md`*, *`file.en.md`*
etc.

Languages must be declared in at least one of the source files. Parts of the text for each
language are enclosed between opening and closing directives, while other directives can
generate tables of contents, number heading levels, add input files or other tasks.

## How fast?<A id="a4"></A>

Writing language parts is very fast and easy. Default text, which is generally the native
language of the original author, doesn't need any specific directive and is written just
like normal text, while language specific parts are simply surrounded by
little open/close directive. For example starting english text is done by writing `.en((`, 
followed by the text, and ended by `.))`. Empty lines, code fences, tables, any text and
Markdown features can be put between these two markers. Headings starting with `#` can include
open/close language directives too, or they can be put between markers if the author like 
it this way.

As of processing speed, MLMD script generates the 2000 lines of its own documentation 
in about 2 seconds on an Intel i7 at 2 GHz, files being written on a SSD.

## Why?<A id="a5"></A>

I designed MLMD because I needed to write a technical documentation both in English and in
French for a project. Looking on the Web I found a very efficient Python script from Hong-ryul Jong
at https://github.com/ryul1206/multilingual-markdown. However after using it a little I found
I needed some more features and I wanted to design a set of directives which would keep the
text more readable and easier to type than HTML comment tags. I kept most of basic Hong-ryul ideas like
ignored text, language declaration but also worked the design so directives could be put
right into the text and not using the HTML syntax. I also designed various output modes to
adjust for different HTML or pure Markdown contexts.

MLMD lets the user write default text when no language specific translation is available, 
put sophisticated numbering schemes for heading levels in all or each file, generate global
or local table of contents, include other files, escape text, use a few variables to ease
language-independent linking to other files and many other tasks.

Special features let the writer separate language parts or stream them as he/ or she likes.

Writing MLMD files is almost as easy as writing Markdown files. MLMD is UTF-8 by nature and
will accept any language and characters sets in a same file.

MLMD is a convenient way of keeping the documentation structure in source files while permitting
translation by different authors in a unique and shared place. And being text files, the MLMD
multilingual sources work very well with versioning systems like Git, allowing diff comparisons,
pull requests and files merging. 

## Documentation<A id="a6"></A>

Full documentation can be found in [the `docs` directory](docs/MLMD.md) under this file.

The sources for the README documentation you are reading can be found in [this root directory](README.mlmd).

The documentation source is written in MLMD itself and can be found in [the `docsource` directory](docsource/MLMD.mlmd).
It is a comprehensive example of MLMD source possible writing styles and directives uses.

## Enjoy!<A id="a7"></A>

I hope MLMD will help you to easily maintain multilingual documentations.

In these Covid days, please stay safe and protect others.

Francis Piérot

August-December 2020
To my father Serge, 1932-2020
I learned from him that an engineer works hard to work less.

[Français](README.fr.md) - [English](README.md)
