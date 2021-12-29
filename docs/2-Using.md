# II) How to Use MLMD<A id="a8"></A>

MLMD is run by the php interpreter and either receives parameters telling it which source
files to process, either processes all the source files it finds in the current directory
from where it has been launched.

Optional parameters tells mlmd where to write generated files, how to number headings or write links.

## II-1) MLMD run parameters<A id="a9"></A>

The syntax for `mlmd.php` and its arguments is:

```code
php <path-to-mlmd>mlmd.php [parameters]
parameters:
    [-i <file_path> [...]]
    [-main <file_path>]
    [-out html|htmlold|md|mdpure]
    [-numbering <numbering_scheme>]
    [-od <path>]
```

If an alias has been set there's no need to explicitly call `php` or give the full path of MLMD script.

The input files can be given to the script with the `-i` parameter, or they can be found in
the current directory from where the script is called if no `-i` parameter is given. This is explained
in [Input files](#input-files--i-argument).

## II-2) Source file paths and names<A id="a10"></A>

The files names for the MLMD source files must end with either `.base.md` or `.mlmd` extension. Files with
other extensions will be ignored. The `.base.md` extension can be convenient because syntax highlighting
and Markdown previewing will work in most editors, however the MLMD sources are not actual Markdown files
and do not fully conform to Markdown syntax so this can lead to some confusion. The `.mlmd` extension is
more explicit and makes it clear that the files are MLMD sources rather than variants of Markdown files,
and text editors can generally be configured to recognized MLMD syntax.

When no source files parameter (`-i`) is given to the script, MLMD will explore the directory tree where
it starts and generate files for all the sources it finds and the languages declared in them. The generated
files will be put in the same directory as their source file.

The `-main` parameter sets the main input file and the root directory for all relative links in the
generated files: the directory of this main file will be considered as the root directory for all other files.
For consistency, no other file should lie above this root directory or in a directory outside the tree under
this root, so that all internal links in generated files can use relative paths.

The various directives are described in [Directives](#directives).

## II-3) Input files: `-i` argument<A id="a11"></A>

To process specific files, use the `-i` parameter followed by the files paths. To process more than one files,
it is best to have them in a same tree and to start MLMD at the root directory where the main Markdown file lies so
MLMD will find all the source files. In this case the `-i` argument is not needed.

- Process a given file: use `-i <template_path>`:

  ```code
  php ~/phpscripts/mlmd.php -i ~/project/README.mlmd
  ```

- Process multiple files: use multiples `-i <template_path>`:

  ```code
  php ~/phpscripts/mlmd.php -i ~/project/README.mlmd -i ~/project/HOWTOUSE.mlmd
  ```

- Process a whole directory and subdirectories: change to this directory and don't give any `-i` parameter:

  ```code
  cd ~/project
  php ~/phpscripts/mlmd.php
  ```

  This syntax will process any file found in the directory tree which ends by `.base.md` or `.mlmd`,
  including those found in sub directories. Other files will be ignored.

## II-4) Main file: `-main` argument<A id="a12"></A>

If a file is named `README.mlmd` in the directory where the script is started, it will be considered
the main file of all the directory tree and all the links will use paths relative to its directory.
Notice the name casing: `README` is uppercase, while the `.mlmd` extension is lowercase. On Windows,
case is not significant but the script still searches an uppercase `README`.

If there is no `README.md` file in the starting directory, the `-main` parameter can be used to tell
the script which source is the main file, and indirectly which directory is the root directory:

```code
php ~/phpscripts/mlmd.php -main ./main.mlmd
```

The main file is generally the bets place where to put a global table of contents covering
all the source files. See the `.toc` directive for more informations.

## II-5) Output mode html/htmlold/md/mdpure: `-out` argument<A id="a13"></A>

The `-out` parameter selects the HTML or Markdown syntax for the links in the Tables Of Contents.

Markdown allows a few ways for creating links to a heading in a file:

- use standard HTML `<A>` anchors and links, using the `name` or `id` attribute to identify anchors.
- use Markdown automatic heading targets, all lowercase with non alphanumeric characters deleted and spaces
  changed to dashes.
- use Markdown `{:` targets in headings: this style is not recognized by all Markdown editors and
  viewers and may not work very well.

The standard old style HTML `<A name="target"></A>` or modern style HTML `<A id="target"></A>` anchors
and `<A href="file#target"></A>` links can be used in MLMD sources just like they would in standard HTML
or Markdown. The Markdown automatic links `[](#heading-text)` also works as they would in a normal
Markdown: MLMD won't change the anchors and links written using these forms. However this needs a change
in titles text in the automatic link as they must be cleaned from any space or non alphanumeric characters.

Common use standards for Markdown discourage the use of HTML, however it is perfectly valid to use HTML and
HTML anchors are more versatile and reliable than Markdown links which are not always correctly handled by
Markdown files viewers or editors.

That said, MLMD can generate a Table Of Contents using the `.toc` directive and will put links to headings
in this table. To help with adopted conventions, MLMD allow the choice for anchors and links styles it places in the
table of contents links and in file headings. This is done using the `-out` parameter.

| Parameter        | Headings anchors      | TOC links                  |
|---------------------|-----------------------|----------------------------|
| `-out htmlold`      | `<A name="target">`   | `<A href="file#target">`   |
| `-out html`         | `<A id="target">`     | `<A href="file#target">`   |
| `-out md`           | `<A id="target">`     | `[](file#target)`          |
| `-out mdpure`       | `{#id}`               | `[](file#target)`          |

All the generated identifiers in anchors are guaranteed globally unique over the processed files by MLMD.

There is no absolute best method, the choice for the right method is context dependent. To try
another mode it is best to run MLMD on source files changing only the `-out`parameter value and check
if the result is as expected.

### II-5.1) Named HTML anchors: `htmlold`<A id="a14"></A>

This mode uses plain old HTML style `<a name="id">` anchors to headings targets and `<a href>` links
in the TOC. It is best used in vanilla HTML context for existing documentation or system, to keep maximum
compatibility with possibly old Web browsers.

### II-5.2) Identified HTML anchors: `html`<A id="a15"></A>

Recent standards have replaced the `name` attribute in HTML `<A>` anchors by the `id` attribute,
which has the benefit of automatic interfacing with Javascript. This mode uses the new `id` attribute
for anchors and `<a href>` links in the TOC and is well suited for HTML documentation in a modern,
dynamic scripted environment.

### II-5.3) Anchored Markdown: `md`<A id="a16"></A>

This hybrid mode uses HTML anchors with the `id` attribute and `[]()` Markdown links in the TOC.
It is appropriate for software or Github documentation and works in a lot of different situations
where HTML is allowed.

### II-5.4) Pure Markdown: `mdpure`<A id="a17"></A>

This mode generate Markdown `{#}` anchors for headings and uses `[]()` Markdown links in the TOC
and use no HTML construction at all. It is well suited in pure Markdown contexts or when files are
automatically checked against Markdown conformity. However Markdown anchors may not work in all
Markdown processors so if this is a problem, the `md` hybrid mode can be a better choice.

### II-5.5) About non-unique headings<A id="a18"></A>

Because of the automatic headings links feature, Markdown convention is generally forbidding identical
heading texts in a file. However, except for a warning from Markdown lint tools, identical headings are
not an actual issue for MLMD if they are not targeted by any automatic link. MLMD allocates unique anchor
identifiers over all the processed files so even identical headings can be targeted unambiguously in the TOC.
However the user cannot know the MLMD unique identifier before all files are processed, so links in the text
body cannot easily use the MLMD anchors.

## II-6) Headings numbering: `-numbering`<A id="a19"></A>

The `-numbering` parameter sets a numbering scheme for headings levels over all the generated files and in the
tables of contents. For example, a third level heading could be numbered `A.2-5) Using objects`. The numbering
can be set in two ways:

- globally for all generated files, using the `-numbering` script argument
- globally in the main file using the `.numbering` directive
- file by file using the `.numbering` directive.

The script argument has priority and will make MLMD ignore any file `.numbering` directive. The following
addresses the script argument, the syntax is identical for the file directive and is addressed later.

### II-6.1) Syntax<A id="a20"></A>

The parameter consists of any number of levels definitions separated by a comma:

```code
-numbering [<level>]:[<prefix>]:<symbol>[<separator>][,...]]
```

Following are details about each definition part.

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

### II-6.2) Example<A id="a21"></A>

This is how to number level 1 headings with the 'A', 'B' etc letters followed by a dash `-`,
then add a number followed by a dot `.` for level 2 headings, then add a number for
level 3 headings:

```code
-numbering 1:Chapter :A-,2::1.,3::1
```

- Levels 4 and above headings will not be numbered and will not appear
  in table of Contents if the .toc directive doesn't ask for them. If
  they appear in TOC, they will use a dash '`-`' as prefix.
- The first level 1 heading will be prefixed and appear as `Chapter A)`.
- Level 2 headings will be numbered `A-1)`, `A-2)` etc. The level 1 prefix
  doesn't apply to level 2 numbering.
- Level 3 headings will be numbered `A-1.1`, then `A-1.2`, `A-1.3` etc.

Only the level 1 can use a prefix. Although prefixes can be supplied
for other levels in the numbering scheme, they will have no effect.
