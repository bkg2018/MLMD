# MLMD - Multilingual Markdown Generator<A id="a1"></A>
[Français](README.fr.md) - [English](README.md)

MLMD generates one or more Markdown files for a set of declared languages from one or more
multilingual source files UTF-8 encoded, using directives in the files to distinguish each
language parts.

MLMD is a convenient way of keeping the documentation structure in unique files without having
to duplicate them for each target language, and permits the translation by different authors while
keeping the file in a unique and shared place. Being text files, the MLMD multilingual sources
work well with versioning systems like Git. 

MLMD can add tables of content (TOC) in the generated Markdown files and allow a numbering scheme
to be applied on all headings in all files and in tables of content. Table of contents can be
global and include headings from all the input files, or they can be local to their own file.

The user has control over the generated languages, the table of content generation and the
headings numbering scheme using either command line parameters or directives in source files.

![File generation and directives](https://github.com/bkg2018/MLMD/blob/main/docs/Images/File%20Generation.png)

## Table Of Contents<A id="toc"></A>

- [MLMD - Multilingual Markdown Generator](#a1)
- I) [Installation](1-Installation.md#a2)
  - I-1) [PHP version](1-Installation.md#a3)
  - I-2) [Storing MLMD](1-Installation.md#a4)
  - I-3) [Using an alias to launch MLMD](1-Installation.md#a5)
    - I-3.1) [Linux / macOS / OS X](1-Installation.md#a6)
    - I-3.2) [Windows 10](1-Installation.md#a7)
- II) [How to Use MLMD](2-Using.md#a8)
  - II-1) [MLMD run parameters](2-Using.md#a9)
  - II-2) [Source file paths and names](2-Using.md#a10)
  - II-3) [Input files: `-i` argument](2-Using.md#a11)
  - II-4) [Main file: `-main` argument](2-Using.md#a12)
  - II-5) [Output mode html/htmlold/md/mdpure: `-out` argument](2-Using.md#a13)
    - II-5.1) [Named HTML anchors: `htmlold`](2-Using.md#a14)
    - II-5.2) [Identified HTML anchors: `html`](2-Using.md#a15)
    - II-5.3) [Anchored Markdown: `md`](2-Using.md#a16)
    - II-5.4) [Pure Markdown: `mdpure`](2-Using.md#a17)
    - II-5.5) [About non-unique headings](2-Using.md#a18)
  - II-6) [Headings numbering: `-numbering`](2-Using.md#a19)
    - II-6.1) [Syntax](2-Using.md#a20)
    - II-6.2) [Example](2-Using.md#a21)
- III) [Writing source files](3-Writing.md#a22)
  - III-1) [Source beginning](3-Writing.md#a23)
  - III-2) [Including source files](3-Writing.md#a24)
    - III-2.1) [Include directive](3-Writing.md#a25)
    - III-2.2) [Numbering main and included files](3-Writing.md#a26)
  - III-3) [Headings](3-Writing.md#a27)
  - III-4) [End-of-Lines and End-of-Paragraphs](3-Writing.md#a28)
    - III-4.1) [Notes](3-Writing.md#a29)
  - III-5) [Multi-line blocks (lists, quotes, tables)](3-Writing.md#a30)
  - III-6) [Escaping text](3-Writing.md#a31)
  - III-7) [Quoted text and code fences](3-Writing.md#a32)
  - III-8) [Variables](3-Writing.md#a33)
  - III-9) [Default text](3-Writing.md#a34)
  - III-10) [Avoiding ambiguities](3-Writing.md#a35)
  - III-11) [Directives](3-Writing.md#a36)
  - III-12) [Immediate vs enclosed effect](3-Writing.md#a37)
  - III-13) [Default directives values and effects](3-Writing.md#a38)
- IV) [Directives Reference](4-Directives.md#a39)
  - IV-1) [Declaring languages: `.languages`](4-Directives.md#a40)
    - IV-1.1) [Syntax](4-Directives.md#a41)
    - IV-1.2) [Notices](4-Directives.md#a42)
    - IV-1.3) [Example](4-Directives.md#a43)
  - IV-2) [Defining a numbering scheme: `.numbering`](4-Directives.md#a44)
    - IV-2.1) [Syntax](4-Directives.md#a45)
  - IV-3) [Numbering level 1 heading: `topnumber`](4-Directives.md#a46)
    - IV-3.1) [Syntax](4-Directives.md#a47)
  - IV-4) [Generating Table Of Content: `.toc`](4-Directives.md#a48)
    - IV-4.1) [Syntax](4-Directives.md#a49)
    - IV-4.2) [Examples](4-Directives.md#a52)
  - IV-5) [Generating for all languages: `.all((`](4-Directives.md#a53)
    - IV-5.1) [Syntax](4-Directives.md#a54)
    - IV-5.2) [Examples](4-Directives.md#a55)
  - IV-6) [Default text: `.default((` or `.((`](4-Directives.md#a56)
    - IV-6.1) [Syntax](4-Directives.md#a57)
    - IV-6.2) [Examples](4-Directives.md#a58)
  - IV-7) [Ignoring text: `.ignore` or `.!((`](4-Directives.md#a59)
    - IV-7.1) [Syntax](4-Directives.md#a60)
    - IV-7.2) [Example](4-Directives.md#a61)
  - IV-8) [Generating for languages: `.<code>((`](4-Directives.md#a62)
    - IV-8.1) [Syntax](4-Directives.md#a63)
    - IV-8.2) [Examples](4-Directives.md#a64)
  - IV-9) [Escaping text: `.{` and `.}`](4-Directives.md#a65)
  - IV-10) [Examples](4-Directives.md#a66)
- V) [Debugging source files](5-Debugging.md#a67)
  - V-1) [DON'T FIX GENERATED FILES](5-Debugging.md#a68)
  - V-2) [Trace mode: `-trace`](5-Debugging.md#a69)
  - V-3) [Unclosed sections](5-Debugging.md#a70)
  - V-4) [Wrong indentations](5-Debugging.md#a71)
  - V-5) [Wrong language](5-Debugging.md#a72)
  - V-6) [Misplaced default text](5-Debugging.md#a73)
  - V-7) [Inconsistent Markdown lists and tables](5-Debugging.md#a74)
  - V-8) [Wrong headings numbering](5-Debugging.md#a75)
  - V-9) [Disappearing ending period](5-Debugging.md#a76)
  - V-10) [Spellchecking](5-Debugging.md#a77)
- VI) [Conclusion](6-Conclusion.md#a78)

I hope MLMD will help you to easily maintain multilingual documentations.

In these Covid days, please stay safe and protect others.

Francis Piérot

August-December 2020<br />
To my father Serge, 1932-2020<br />
He taught me an engineer works hard at working less.

[Français](README.fr.md) - [English](README.md)
