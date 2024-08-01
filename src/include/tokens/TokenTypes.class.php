<?php

/**
 * Static class for token types.
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
 * @package   mlmd_token_types_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {
    /**
     * Class for the token types.
     */
    class TokenType
    {
        public const UNKNOWN                = 0;
        public const FIRST                  = 0;

        public const SINGLE_LINE_DIRECTIVE  = self::FIRST + 1;
        public const OPEN_DIRECTIVE         = self::FIRST + 2;
        public const CLOSE_DIRECTIVE        = self::FIRST + 3;
        public const TEXT                   = self::FIRST + 4;
        public const ESCAPED_TEXT           = self::FIRST + 5;
        public const EOL                    = self::FIRST + 6;
        public const EMPTY_LINE             = self::FIRST + 7;
        public const SPACE                  = self::FIRST + 8;
        public const END_FILE               = self::FIRST + 9;
        public const HEADING                = self::FIRST + 10;
        public const INLINE_DIRECTIVE       = self::FIRST + 11;

        public const LAST                   = self::FIRST + 11; // keep identical to last line above

        public static function getName($index): string {
            switch ($index) {
            case self::SINGLE_LINE_DIRECTIVE: return "SINGLE_LINE_DIRECTIVE";
            case self::OPEN_DIRECTIVE: return "OPEN_DIRECTIVE";
            case self::CLOSE_DIRECTIVE: return "CLOSE_DIRECTIVE";
            case self::TEXT: return "TEXT";
            case self::ESCAPED_TEXT: return "ESCAPED_TEXT";
            case self::EOL: return "EOL";
            case self::EMPTY_LINE: return "EMPTY_LINE";
            case self::SPACE: return "SPACE";
            case self::END_FILE: return "END_FILE";
            case self::HEADING: return "HEADING";
			case self::INLINE_DIRECTIVE: return "INLINE_DIRECTIVE";
            }
            return "unknown";
        }
    }
}
