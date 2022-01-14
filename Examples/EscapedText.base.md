# Escaping text

MLMD accepts all Markdown text-escaping markers plus some. In the escaped text, MLMD directives and variables are not interpreted and will be written exactly as they are in source files, e.g. `{extension}` in source file will be written `{extension}` in the generated files.

Code fences are allowed: they start with three backticks characters followed by a language name or any code which is significant for the destination Markdown viewer so it can adjust syntax-corloring or any other checks. Then comes the lines for escaped text, and the whole block is finished by a line with only three backticks. 

```code
Example of pseudo code
```

Escaped text can also be surrouded by Markdown and MLMD markers:

* `one` backtick
* ``two`` backticks
* ```three``` backticks
* "double quotes"
* MLMD open and close markers `.{` and `.}`

MLMD specific markers `.{` and `.}` are not written into generated files. All the others are written so Markdown will interpret them in generated files.

