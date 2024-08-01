<?php

/**
 * Multilingual Markdown generator - TokenPicture class
 *
 * This class represents a token for a .pic((<path>)) or .picture((<path>)) directive in text flow.
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
 * @package   mlmd_token_language_directive_class
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
     * .picture(( directive token.
     */
    class TokenPicture extends TokenBaseInline
    {
        private $picture = '';

        public function __construct(string $name)
        {
            parent::__construct(TokenType::INLINE_DIRECTIVE, ".{$name}((", true);
        }

        /**
         * Process input by skipping the directive and handling picture path.
         */
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $this->skipSelf($input);
            $input->adjustNextLine();
            $endKeyword = '))';
            $currentChar = $input->getCurrentChar();
            $prevChars = '';
            if ($currentChar != null) {
                do {
                    $this->picture .= $currentChar;
                    $currentChar = $input->getNextChar();
                    $prevChars = $input->fetchPreviousChars(2);
                } while (($prevChars != $endKeyword) && ($currentChar != null));
            }
            $this->picture = mb_strcut($this->picture, 0, -2);
            $this->length = mb_strlen($this->picture);
            $lexer->appendToken($this, $filer);
            $lexer->setCurrentChar($currentChar);
        }

        /**
         * Output sends the variable {picture:} with the image reference. It will
         * be expanded (and copied) depending on default or current language.
         */
        public function output(Lexer &$lexer, Filer &$filer): bool
        {
            return $filer->output('{picture:' . $this->picture . '}', true, $this->type);
        }
    }
}
