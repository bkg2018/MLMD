<?php

/**
 * Multilingual Markdown generator - TokenText class
 *
 * This class represents a token for normal text. In normal text output, variables are expanded.
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
 * @package   mlmd_token_text_class
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
     * Token for text.
     */
    class TokenText extends Token
    {
        protected $content; /// text content for this token, including spaces and EOLs
        protected $length;  /// number of UTF-8 characters

        public function __construct($content)
        {
            parent::__construct(TokenType::TEXT);
            $this->content = $content;
            $this->length = mb_strlen($content);
        }
        /**
         * Tells if a token has a content and should be instanciated.

        public function hasContent(): bool
        {
            return true;
        }
         */        
        /**
         * Tell if the token is empty of significant text content.
         *
         * @return bool true if the token has *no* text content.
         */
        public function isEmpty(): bool
        {
            return ($this->length <= 0);
        }
        
        /**
         * Add a character or string to content.
         *
         * @param string $c the character or string to add.
         */
        public function addChar(string $c): void
        {
            $this->content .= $c;
            $this->length = mb_strlen($this->content);
        }

        /**
         * Return the content.
         */
        public function getText(): string
        {
            return $this->content;
        }

        /**
         * Return the number of UTF-8 characters in content.
         */
        public function getTextLength(): int
        {
            return $this->length;
        }

        /**
         * Check if content is uniquely composed of spacing characters.
         * NB this doesn't handle UTF-8 spacing.
         */
        public function isSpacing(): bool
        {
            return \ctype_space($this->content);
        }

        /**
         * Output the content with variables expanding.
         */
        public function output(Lexer &$lexer, Filer &$filer): bool
        {
            return $filer->output($this->content, true, $this->type, $lexer->isOutputTraced());
        }
    }
}
