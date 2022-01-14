# CHANGELOG

## Version 1.0.8 / 2022-01-10

- changed escaped text directive markers from .{ .} to .! .! because of syntaxic side effect with {} variables markers
- fixed end of file bug causing a crash with null assignment
- doc and comments updates for new .! directive
- added warnings for unclosed escaped text
- added warning wen no .languages directive found

## Version 1.0.7 / 2021-12-29

- minor doc updates
- version system changed for src/Version.php
- compatibility with php 8.1
- fixed TOC links for unnumbered schemes

## Version 1.0.5 / 2021-10-02

- minor doc updates
- check for mbstring extension at startup
- false 'iso code' warnings due to inversion of parameters in one strpos() call
- fixed crashes when no numbering scheme was set

## Version 1.0.4 / 2020-12-15

- doc updates and french typos
- doc explanations about Markdown multi line blocks (quotes, tables, lists)
- started work on multi,lines handling by adding language in TokenClose
- renamed isMLMDfile in getMLMDExtension
- added {filename} variable to get current file basename
  
## Version 1.0.3 

- initial deposit, first working version
- applied on MLMD doc and README themselves