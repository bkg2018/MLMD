<?php

/**
 * Multilingual Markdown generator - TokenClose class
 *
 * This class is the token for the .)) language ending directive.
 * It closes the previous opening .<code>(( directive and restores previous language
 * from the Lexer language stack.
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
 * @package   mlmd_token_end_directive_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'TokenBaseInline.class.php';

    use MultilingualMarkdown\TokenBaseInline;
    
    /**
     * .)) directive token.
     */
    class TokenClose extends TokenBaseInline
    {
        public function __construct()
        {
            parent::__construct(TokenType::CLOSE_DIRECTIVE, '.))', true);
        }

        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $this->skipSelf($input);
            if ($lexer->popLanguage($filer)) {
                $lexer->appendToken($this, $filer);
            }
            $currentChar = $input->getCurrentChar();
            $lexer->setCurrentChar($currentChar);
        }
        // Output: have Lexer updating the current output language
        public function output(Lexer &$lexer, Filer &$filer): bool
        {
            $lexer->popLanguage($filer);
            return true;
        }
    }
}
