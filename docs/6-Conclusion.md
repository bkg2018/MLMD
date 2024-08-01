# VI) Conclusion<A id="a78"></A>

Use MLMD to write multilingual documentations sources instead of maintaining different Markdown
files for each language, then generate each language Markdown files from each source.

Here are some use notes.

- Files
  - Source files must be UTF-8 encoded
  - Use the `.mlmd` extension to distinguish MLMD sources from actual Markdown files
  - Use the `-numbering` script argument to consistently number headings over all files and in TOCs
  - Use the '-out' script argument to have anchors and links adapted to your MD or HTML context
  - Use `-i` script arguments to choose the files to process, or omit `-i` to process all the
    source files in current directory and subdirectories
  - Use the `-od` parameter to generate files in a chosen directory

- Languages and sections
  - Language codes are declared in a `.languages` directive
  - Codes are global to all the source files
  - An optional ISO code can be associated to each language code
  - Any text before the first `.languages` directive is ignored
  - Any language declared with code `<code>` has an opening directive `.<code>((`
  - Any language section must be closed with `.))`
  - Any text outside open/close directives is default text
  - Or use `.((` to open a default text section
  - Default text section are written to all language file for which no language specific section exists
  - Use `.all((` to unconditionally send text into all languages files
  - Use `.!((` to ignore text

- Headings and text
  - Headings must have a `#` Markdown prefix
  - Put default text for each paragraph and heading before opening languages specific sections
  - Close each opened section with a `.))`
  - Use one end-of-line between directives to visually separate different sections
  - Use the variables to put language specific links or images in text body
  - Markdown styles `===` and `---` for level 1 and 2 are not recognized

- Table Of Contents (`.toc` directive)
  - Use `level=1` to generate a global TOC with links to all the processed sources
  - Use levels 2 to 9 for a local TOC with links to headings in the file
  - Give your `.toc` directive a title, it will become a level 2 heading in current file
  - Place your `.toc` directive after a level 1 heading and introductory text
  - Use links to `#toc` anchor to place a link to the TOC in your text
