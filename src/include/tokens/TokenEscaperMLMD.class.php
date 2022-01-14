<?php

/**
 * Multilingual Markdown generator - TokenEscaperMLMD class
 *
 * This class represents a token for MLMD escaped text between '.!' markers. This syntax allows MLMD content
 * to use any special characters without bothering about variable expansion or directives interpretation.
 * MLMD escaped text may contain normal MD escaping notations as well as MLMD directives or variables between
 * accolades. This is used in MLMD documentation itself to avoid interpretation of directives when the desired
 * effect is to have them written into the final output files.
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
 * @package   mlmd_token_escaper_mlmd_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'TokenBaseEscaper.class.php';

    use MultilingualMarkdown\TokenBaseEscaper;
    
    /**
     * Class for the MLMD escaper token.
     * Starts with '.!' and runs until '.!' if found. Start and end symbols are not put into the content.
     */
    class TokenEscaperMLMD extends TokenBaseEscaper
    {
        public function __construct()
        {
            parent::__construct('.!');
        }
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $this->content = '';
            $end = '.!';
            $wrong = '!';
            $closed = false;
            $this->skipSelf($input);
            $fromStorage = \get_class($input) == 'MultilingualMarkdown\\Storage';
            do {
                if ($input->isMatchingWord($end, 2)) {
                    $input->getNextChar();// skip end marker
                    $input->getNextChar();// skip end marker
                    $currentChar = $input->getCurrentChar();
                    $closed = true;
                    break;
                }
                if ($input->adjustNextLine()) {
                    $this->content .= "\n";
                    $currentChar = $input->getCurrentChar();
                } else {
                    $this->content .= $input->getCurrentChar();
                    $currentChar = $input->getNextChar();
                }
                // on $input end (end of line), switch to next line from $filer
                if ($currentChar == null && $fromStorage) {
                    $line = $filer->getLine();
                    // exit if end of input
                    IF ($line != null) {
                        $input->setInputBuffer($line);
                        $currentChar = $input->getCurrentChar();
                    }
                }
            } while ($currentChar != null);
            $this->length = mb_strlen($this->content);
            $lexer->appendToken($this, $filer);
            if (!$closed) {
                $filer->warning("a '.!' has no matching '.!'");
            }
        }
    }

}
