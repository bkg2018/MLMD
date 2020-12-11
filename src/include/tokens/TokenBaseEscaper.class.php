<?php

/**
 * Multilingual Markdown generator - TokenBaseEscaper class
 *
 * This class is base for all the tokens containing escaped text. All escaper tokens
 * have a text content and an identifier which starts and ends the escape sequence.
 * The text content will be output to files with no variables or directive interpretation.
 *
 * Purely Markdown escape sequences are output with the opening and closing sequence,
 * but there is also the .{.} special escape sequence which is specific to MLMD. This sequence
 * outputs the escaped text without ythe escape markers. See TokenEscaperMLMD for details.
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
 * @package   mlmd_token_base_escaper_class
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
     * Class for an escaper starting/ending.
     *
     * This class will store escaped text tokens by parsing until its closing marker.
     * This is why there are only opening escaper tokens and no closing ones.
     * The content text will be output as is with no variable expansion and no
     * directive interpretation.
     *
     * Escaping can be done with multiple backtics as well as unique ones, so the identification
     * must be checked by trying triple first, then double, then single bacticks in this order.
     * This is the only place where identification order is significant.
     */
    class TokenBaseEscaper extends TokenBaseKeyworded
    {
        protected $content = '';/// the escaped text, including opening and closing escapers
        protected $length = 0;  /// character length of content

        public function __construct(string $marker)
        {
            parent::__construct(TokenType::ESCAPED_TEXT, $marker, true);
        }
        /**
         * Tells if a token has a content and should be instanciated.
        public function hasContent(): bool
        {
            return true;
        }         */

        /**
         * Check if content is uniquely composed of spacing characters.
         * NB this doesn't handle UTF-8 spacing.
         */
        public function isSpacing(): bool
        {
            return \ctype_space($this->content);
        }
        
        /**
         * Return a new instance of same class with same keyword
         */
        public function newInstance(): object
        {
            $class = \get_class($this);
            return new $class($this->keyword);
        }

        /**
         * Return true when asked for TokenType::ESCAPED_TEXT.
         * Accepts an array of token types or a single one.
         *
         * @param array|TokenType $type the token type to test, or an array of token types
         * @return true if the token type(s) is ESCAPED_TEXT.
         */
        public function isType($type): bool
        {
            if ((\is_array($type) && \in_array(TokenType::ESCAPED_TEXT, $type)) || ($type == 'TokenType::ESCAPED_TEXT')) {
                return true;
            }
            return parent::isType($type);
        }

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
         * Process input: get text until we find the closing escape marker.
         * Update tokens array with the token itself. The escaped text is stored
         * by the token.
         */
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $this->content = $this->keyword;
            $this->skipSelf($input);
            $input->adjustNextLine();
            $currentChar = $input->getCurrentChar();
            $prevChars = '';
            if ($currentChar != null) {
                do {
                    $this->content .= $currentChar;
                    $currentChar = $input->getNextChar();
                    $prevChars = $input->fetchPreviousChars($this->keywordLength);
                } while (($prevChars != $this->keyword) && ($currentChar != null));
            }
            $this->length = mb_strlen($this->content);
            $lexer->appendToken($this, $filer);
            $lexer->setCurrentChar($currentChar);
        }
        
        /**
         * Output content.
         */
        public function output(Lexer &$lexer, Filer &$filer): bool
        {
            return $filer->output($this->content, false, $this->type);
        }
    }
}
