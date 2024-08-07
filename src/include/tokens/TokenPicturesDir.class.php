<?php

/**
 * Multilingual Markdown generator - TokenPicturesDir class
 *
 * This class represents a token for the pictures directory .picturesdir directive.
 * It sets a root directory in the pictures manager.
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
 * @package   mlmd_token_picturesdir_directive_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'TokenBaseSingleLine.class.php';

    use MultilingualMarkdown\TokenBaseSingleLine;

    /**
     * .picturesdir(( directive token.
     */
    class TokenPicturesDir extends TokenBaseSingleLine
    {
        private  $lexer;
        private  $directory;

        public function __construct(Lexer $lexer)
        {
            $this->lexer = $lexer;
            parent::__construct(TokenType::SINGLE_LINE_DIRECTIVE, ".picturesdir", true);
        }

        /**
         * Process input by setting the directory into the pictures manager
         */
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $this->skipSelf($input);
            $content = trim($input->getLine());
            $lexer->SetPicturesDir($content);
        }
    }
}
