<?php

/**
 * Multilingual Markdown generator - TokenOpenDefault class
 *
 * This class represents a token for the default text opening directive .(( or .default((.
 * Text out of language open/close directives also goes into default output, so this directive
 * is generally not needed.
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
 * @package   mlmd_token_default_directive_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'TokenOpenLanguage.class.php';

    use MultilingualMarkdown\TokenOpenLanguage;
    
    /**
     * .default(( or .(( directive token.
     */
    class TokenOpenDefault extends TokenOpenLanguage
    {
        public function __construct(string $keyword)
        {
            parent::__construct($keyword);
        }

                /**
         * Process input by skkipping the directive and pushing the language
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


        public function output(Lexer &$lexer, Filer &$filer): bool
        {
            $lexer->pushLanguage(DEFLT, $filer);
            return true;
        }
    }

}
