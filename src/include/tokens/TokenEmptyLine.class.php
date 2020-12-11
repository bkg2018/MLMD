<?php

/**
 * Multilingual Markdown generator - TokenEmptyLine class
 *
 * This class represents a token for an empty line. It is converted
 * into two successive EOLs.
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
 * @package   mlmd_token_empty_line_class
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
     * Class for end of paragraph, or empty line.
     * This class is almost identical to TokenEOL but used in a different context.
     * All empty lines are considered as an end of paragraph in Markdown.
     */
    class TokenEmptyLine extends TokenBaseKeyworded
    {
        public function __construct()
        {
            parent::__construct(TokenType::EMPTY_LINE, "\n", true);
        }

        /**
         * Check beginning of line before checking the key marker.
         */
        public function identifyInBuffer(string $buffer, int $pos): bool
        {
            $prevChar = ($pos > 0) ? mb_substr($buffer, $pos - 1, 1) : "\n";
            if ($prevChar != "\n") {
                return false;
            }
            return parent::identifyInBuffer($buffer, $pos);
        }
        /**
         * Check beginning of line before checking the key marker.
         *
         * @param object $input the Filer or Storage object
         */
        public function identify(object $input): bool
        {
            $prevChar = $input->getPrevChar();
            if ($prevChar != "\n") {
                return false;
            }
            return parent::identify($input);
        }
        /**
         * Processing input: append an EOL token, goto next line.
         */
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $input->gotoNextLine();
            $lexer->appendTokenEOL($filer);
        }
    }
}
