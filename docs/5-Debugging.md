# V) Debugging source files<A id="a67"></A>

MLMD directives and command line arguments build a kind of programming language with which
you write source files, that you compile into generated Markdown files.

Like with all programming language, the writer sometimes put *bugs* in his/her source files.
MLMD generator do its best to help find and fix these bugs but there also exists a few
technics to limit the risks.

This part will explain the tools MLMD gives, the possible bugs and the technics to avoid them.

But first, an important advice.

## V-1) DON'T FIX GENERATED FILES<A id="a68"></A>

If you ever fix the generated french, english, polish, korean or any language specific generated files,
you will find your errors back the next time you will run MLMD on source files.

The right place where to fix the errors is in the source files.

Read your generated files attentively, then notice the errors, then go to the source files to fix them
and finally generate files again with MLMD.

## V-2) Trace mode: `-trace`<A id="a69"></A>

The `-trace` optional arguments will display a trace of all lines as they are read, as well as
some more informations. If the generated files are not as expected, the first thing to do is trying
to follow what's displayed by the trace mode: MLMD displays each line with its number, except for
code fences (`.!```.!`) which are jumped over with only the first line displayed.

Make sure every line is read and the code fences are matched with each other: missing an ending fence
would make the following text disappear from trace until the next code fence or the end of file.

## V-3) Unclosed sections<A id="a70"></A>

At the end of each source file, MLMD will check if all language sections have been closed and will
display the starting line of any non-closed sections. Make sure you read these warnings and fix them
by adding closing directives `.))` at the right places.

## V-4) Wrong indentations<A id="a71"></A>

MLMD will reproduce space indentation only if it occurs *after*  language opening directives, so if
indentation disappear first check if it is correctly placed:

```code
   .fr((Text.))
```

will generate non-indented `text` in the `.fr.md` file. To keep indentation, this line must be written
this way:

```code
.fr((   Text.))
```

## V-5) Wrong language<A id="a72"></A>

If you find wrong language text in a generated file it generally means the wrong open language
directive has been used or a section has not been closed:

- maybe missing an opening parenthesis : `.fr(` will *not* start a french sections
- maybe wrong code was used: `.enn((' will be unknown if `en` was the intended code
- maybe the previous closing directive was misspelled: `.)` won't close a sections

As a conclusion, when something looks odd about languages, first check each opening and closing
directives around the suspected text.

## V-6) Misplaced default text<A id="a73"></A>

Your supposedly default text doesn't go into the expected language files if it appears
after language specific sections instead of before. Default text always apply to upcoming
sections, not past ones.

Here's a  exemple of wrong ordering:

`.fr((Texte en français.))Default text`

With this order, first the french file will receive `Texte en français`, then the
`Default text' will go into *all* language files, including the french file.

To fix this, default must be put first and language specific sections appear later:

`Default text.fr((Texte en français.))`

This fixed line will have the expected effect: the default text will not go into the french file.

## V-7) Inconsistent Markdown lists and tables<A id="a74"></A>

Markdown allows easy writing of lists using `*` and `-` line start, as well as single lines tables
using specific notations. However with MLMD writing each line or list element for multiple languages
can lead to inconsistent structures because end of lines between close/open directives have a special
meaning to MLMD. A much easier way of doing is to keep complete tables and lists in separate language
sections.

For example the following list tries to translate each line one by one:

```code
.en((- first line in english.)).fr((Première ligne en français.))
.en((- second line in english.)).fr((Deuxième ligne en français.))
```

The problem is that the first end of line will be canceled by MLMD because it's preceding an opening
directive. Instead of this, MLMD recommended structure is to keep language sections complete:

```code
.en((
- first line in english
- second line in english
.)).fr((
- première ligne en français
- deuxième ligne en français
.))
```

It doesn't look as simple but is much more controllable and MLMD will always generate the correct sections
for each language when it finds this kind of structure.

It should be noted that the end of line cancelling is a trade-off for MLMD to make languages sections
visual separations easier.

## V-8) Wrong headings numbering<A id="a75"></A>

If the headings numbering doesn't match what was expected, some possible errors are:

- a mistake about the supposed order in which files are processed which changes the level 1 heading number
  of each file and indirectly, the first number of all headings numbering
- there can be a bug in the  numbering scheme itself, e.G. a missing `:` or `,``
- there can be multiple and inconsistent `.numbering` directives in files

For a best result, all processed files should follow the rules below:

- have the same `.numbering` scheme or don't use one and let `-numbering` command line parameter do the job
- use `.topnumber` to force the level 1 number either to disappear (`.topnumber 0`) either to
have a given value.

Files will be ordered in TOCs and in process either by their `topnumber` value, 
either by the time they have been written in directory first, which is generally not what is
expected and almost never what is displayed by operating systems. The best results are 
definitely obtained by using `.topnumber` in source files.

## V-9) Disappearing ending period<A id="a76"></A>

If an ending period is disappearing from a sentence, chances are that the dot from a closing
directive following the sentence has been mistaken for that period. Dots must be doubled at the end
of a section sentence: the first dot is the ending period for the sentence, then the second dot is
the directive start:

```code
.en((This sentence is not ended by a period.))
.en((This sentence is ended by a period..))
```

## V-10) Spellchecking<A id="a77"></A>

Most text files editor softwares have spellchecking plugins which check te text on the fly as it
is entered. Visual Code for example has extensions for spellchecking in many languages.

If possible, spellchecking should be done on source files, however this can be problematic if 
many languages have different spelling for similar words and the spellchecker won't signal errors.
For example the word `exemple` in english text won't be signaled by the spellchecker if it also
checks the french language.

If reading generated files reveals errors, then spellchecking can be done in the right language
on each generated file, but corrections be done in source files before generating again with MLMD.
