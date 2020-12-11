# Directives et tokens

Token classes hierarchy and their equivalent in MLMD files:

Token +--- TokenBaseKeyworded +--- TokenBaseEscaper +--- TokenEscaperDoubleBacktick:                `` around text
      |                       |                                                                     contains markers and escaped text
      |                       |                     +--- TokenEscaperSingleBacktick:                ` around text
      |                       |                                                                     contains markers and escaped text
      |                       |                     +--- TokenEscaperTripleBacktick:                ``` around text, not at line start
      |                       |                                                                     contains markers and escaped text
      |                       |                     +--- TokenEscaperDoubleQuote:                   " around text
      |                       |                                                                     contains markers and escaped text
      |                       |                     +--- TokenEscaperMLMD:                          { .} around text
      |                       |                                                                     contains only escaped text
      |                       |                     +--- TokenEscaperFence:                         ``` at line start before/after lines
      |                       |                                                                     contains all lines including markers
      |                       |
      |                       +--- TokenBaseInline  +--- TokenClose:                                .)) after an opening token
      |                       |                     +--- TokenOpenLanguage  +--- TokenOpenAll:      .all(( before text for all languages
      |                       |                                             +--- TokenOpenDefault:  .(( before default text
      |                       |                                             +--- TokenOpenIgnore:   .ignore(( before ignored text
      |                       |                                             + instanciated for each language code declared in .languages
      |                       |
      |                       +--- TokenBaseSingleLine  +--- TokenHeading:                          for each line of text beginning with a #
      |                                                 +--- TokenLanguages:                        .languages
      |                                                 +--- TokenNumbering:                        .numbering
      |                                                 +--- TokenTOC:                              .toc
      |                                                 +--- TokenTopNumber:                        .topnumber
      |                       +--- TokenEmptyLine:                                                  transformed in at most 2 EOLs
      |                       +--- TokenEOL:                                                        for each end of line
      +--- TokenText:                                                                               any text except Markdown escape
                                                                                                    Òmarkers and escaped text
