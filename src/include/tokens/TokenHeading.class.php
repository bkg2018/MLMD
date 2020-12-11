<?php

/**
 * Multilingual Markdown generator - TokenHeading class
 *
 * This class represents a token for one heading in files.
 * A heading is a line starting with at least one '#' character.
 * The token is instanciated by Lexer specifically for each heading.
 * The token do ont append itself to Lexer list of tokkens, rather it
 * creates tokens for each part of the generated headings depending
 * on the output mode (mdpure, html etc) and numbering scheme.
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
 * @package   mlmd_token_heading_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'Token.class.php';

    use MultilingualMarkdown\Token;

    /**
     * Heading token.
     *
     * The token stores a copy of the Heading object given at creation: it
     * won't find the heading object itself because the token has no knowledge of
     * the preprocessed datas in Lexer.
     */
    class TokenHeading extends TokenBaseSingleLine
    {
        private $heading = null;

        public function __construct(object $heading)
        {
            $this->heading = $heading;
            parent::__construct(TokenType::HEADING, '#', true);
        }

        /**
         * Processing input :the heading is not stored as a single token
         * but rather as tokenized text and elements.
         */
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            // build a sequence of tokens for each heading parts
            // first put tokens for the '#' prefix, as tokenize() on '#' would infinitely recurse
            $prefixText = '.all((';
            $lexer->tokenize($prefixText, $filer, false); // don't output during this call
            $prefixText = str_repeat('#', $this->heading->getLevel()) . ' ';
            $token = new TokenText($prefixText);
            $lexer->appendToken($token, $filer);
            $prefixText = '.))';
            $lexer->tokenize($prefixText, $filer, false); // don't output during this call
            // then add tokens for the rest
            $text = $lexer->getHeadingText($filer, $this->heading);
            $lexer->tokenize($text, $filer, false); // don't output during this call
            // do NOT append this TokenHeading to Lexer, all the heading line has been
            // cut and stacked as a sequence of other tokens
            $input->gotoNextLine();
            unset($text);
            unset($prefixText);
            unset($token);
        }
    }
}
