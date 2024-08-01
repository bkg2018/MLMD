<?php

/**
 * Multilingual Markdown generator - TokenOpenLanguage class
 *
 * This class represents a token for an opening language code .<code>(( directive. Each language code
 * must have been declared in the .languages directive. The token for each open language directive
 * is instantiated when Lexer pre-processes the .languages directive.
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
     * .<code>(( directive token.
     * This kind of token is created by the .languages directive.
     */
    class TokenOpenLanguage extends TokenBaseInline
    {
        protected $language = ''; // language code from .languages directives

        public function __construct(string $language)
        {
            $this->language = $language;
            parent::__construct(TokenType::OPEN_DIRECTIVE, ".$language((", true);
        }
        protected function setLanguage(string $language)
        {
            $this->language = $language;
        }
        public function getLanguage(): string
        {
            return $this->language;
        }

        /**
         * Process input by skipping the directive and pushing the language
         * on the language stack in lexer.
         */
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $this->skipSelf($input);
            if ($lexer->pushLanguage($this->language, $filer)) {
                $lexer->adjustCloseOpenSequence();
                $lexer->appendToken($this, $filer);
            }
            $currentChar = $input->getCurrentChar();
            $lexer->setCurrentChar($currentChar);
        }

        /**
         * Output updates the Lexer language stack which indirectly sets the current
         * language in Filer.
         */
        public function output(Lexer &$lexer, Filer &$filer): bool
        {
            $lexer->pushLanguage($this->language, $filer);
            return true;
        }
    }
}
