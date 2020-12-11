<?php

/**
 * Multilingual Markdown generator - TokenBaseSingleLine class
 *
 * This base class represents a directive which stands alone on a single line.
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
 * @package   mlmd_token_base_single_line_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'TokenTypes.class.php';
    require_once 'TokenBaseKeyworded.class.php';

    use MultilingualMarkdown\TokenBaseKeyworded;
    
    /**
     * Single line directive token.
     *
     * This class is not instanciated by itself but is base for actual directives tokens.
     */
    class TokenBaseSingleLine extends TokenBaseKeyworded
    {
        public function __construct(int $tokenType, string $keyword, bool $ignoreCase)
        {
            parent::__construct($tokenType, $keyword, $ignoreCase);
        }

        /**
         * Let the token self-identify against an input Filer or Storage object.
         *
         * @param object $input the Filer or Storage object
         *
         * @return bool true if the current token can be found at current Filer position and buffer content.
         */
        public function identify(object $input): bool
        {
            // must be preceded by an end of line or nothing (possible if first line in file)
            $prevChar = $input->getPrevChar();
            if (($prevChar != null) && ($prevChar != "\n")) {
                return false;
            }
            // normal keyword token identification
            return parent::identify($input);
        }

       /**
         * Process one-line directive.
         * Simply ignore the line and go to next line start character.
         *
         * @param Lexer $lexer   the Lexer object
         * @param object $input  the Filer or Storage object ready for input, positionned on the directive
         */
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $input->gotoNextLine();
            $lexer->appendToken($this, $filer);
        }
    }
}
