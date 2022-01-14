<?php

/* Multilingual Markdown generator - global functions
 *
 * Copyright 2020 Francis Piérot
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files
 * (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF
 * OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package   mlmd_functions
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

function displayHelp(): void
{
    echo "
Multilingual Markdown generator - version " . MLMD_VERSION . "

Parameters:

-h  display this help.

[-i] <filepath.mlmd|filepath.base.md>
    add a file to input files. MLMD will generate one <filepath.xx.md> 
    file for each languages 'xx' declared in the '.languages' directive.
    If -i is not used the script assumes the parameter is an input file path.

-out html|md
    choose HTML or MD for links and anchors for Table Of Contents

-main <mainFilename[.mlmd|.base.md]>
    add a main file (supposedly the one with a global TOC including levels 1-n)
    and indirectly set the root directory for all other files

-numbering <def>[,...]
    declare at least one heading numbering definition, where <def> is: 
        [<level>]:<symbol><separator>
        where :
            <level> is 1 to 9 (default = 1 or next level after level of previous def)
            <symbol> is from 'A'..'Z', 'a'..'z', '1'..'9'
            <separator> is a single character e.g. '.' or '-'

-od <directory>
    set the output root directory, in which written files will mirror the 
    input file root directory

If no '-i' and '-main' parameter is given, MLMD explores current and sub directories
for '*.base.md' and '*.mlmd' template files and generates files for each template found.
By default, main file will be README.mlmd or README.base.md if such a file is found 
in current directory.
 
The '-main' option sets the base file name referenced by the {main} variable (see below) and
sets the root directory for all links in all files. Preferably, all the other template files
should be in this root directory or in subdirectories.

Template files must be named with .base.md or .mlmd extension, other extensions are ignored.

Directives in templates control the languages specifics files generation.

Global directives on one line:
- .languages    declares languages codes (global)
- .numbering    sets the heading numbering schemes (global, also available
                as script argument and .toc parameter)
- .toc          generates a table of contents using headings levels
- .topnumber    set the level 1 heading (single '#') logical number
- .end          end the source file
- .stop         help to put a debugging breakpoint in mlmd script

Directives anywhere in the text and in headings:
- .all((        starts a section for all languages
- .ignore((     starts an ignored section
- .!((          identical to .ignore((
- .default((    starts a section for languages which don't have a specific section
- .((           identical to .default((
- .<language>(( starts a section specific to <language>
- .))           ends a section
- .!            encloses escaped text (no variable expansion)

Markdown escape markers are recognized by MLMD and written into output files. The escaped
text will be written exactly as it appears with no language specific transformation and no
variable expanding (see below).

- text can be escaped anywhere between Markdown back-ticks '`', double back_ticks '``', 
  triple back_ticks '```' and double quotes '\"'; The markers are written in output files
- text can be escaped between Markdown code fences '```' at the beginning of a line;
  the surrounding code fence lines are written in output files
- text can be escaped between specific MLMD markers '.!'; the markers
  will not be written in output files

MLMD specific escape markers can be used to surround MLMD directives so they can be
written in output files instead of being interpreted..

When not between escape markers, the following variables are expanded in each generated file:

- {file} expands to the current file name, localized for the language
  ('file.xx.md' for language 'xx', 'file.md' for main language)
- {filename} expands to the current file base name with no extension
  ('file' for source file 'file.mlmd')
- {extension} expands to the current written file extension
  ('.xx.md' for language 'xx', '.md' for main language)
- {main} expands to the '-main' file name, localized for the language
  ('main.xx.md' for language 'xx' or 'main.md' for main language)
- {language} expands to the language code as declared in the
  '.languages' directive ('xx' for language 'xx')
- {iso} expands to the ISO code associated to current language

\n";
}

/**
 * Current version of MLMD.
 */
function displayVersion(): void
{
    global $MLMD_VERSION, $MLMD_DATE;
    echo "MLMD MultiLingual MarkDown Generator\nVersion $MLMD_VERSION - $MLMD_DATE\n";
}

/**
 * Activate trace
 */
function setTrace(MultilingualMarkdown\Generator $generator): void
{
    $generator->setTrace(true);
}
