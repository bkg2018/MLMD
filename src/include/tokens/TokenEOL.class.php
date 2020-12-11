<?php

/**
 * Multilingual Markdown generator - TokenEOL class
 *
 * This class represents a token for an end of line character.
 * EOLs can be ignored when they only separate directives and spaces, but will be included
 * in output text if they are separating other kinds of tokens.
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
 * @package   mlmd_token_eol_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'TokenBaseKeyworded.class.php';

    use MultilingualMarkdown\TokenBaseKeyworded;
    
    /**
     * Class for end-of-line.
     * This token represents the "\n" character which is the end-of-line in UTF-8.
     * The Windows OS also uses "\r" before "\n" but the buffer reading routine
     * will withdraw them.
     */
    class TokenEOL extends TokenBaseKeyworded
    {
        public function __construct()
        {
            parent::__construct(TokenType::EOL, "\n", true);
        }

        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $input->gotoNextLine();
            $lexer->adjustEolCloseEolSequence($filer);
        }
        public function output(Lexer &$lexer, Filer &$filer): bool
        {
            if ($filer->outputStarted()) {
                $filer->output("\n", false, $this->type);
            }
            return true;
        }
    }
}
