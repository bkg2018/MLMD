<?php

/**
 * Multilingual Markdown generator - DEBUG Lexer class
 *
 * Adds debug output to parent lexer normal class.
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
 * @package   mlmd_debuglexer_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'Lexer.class.php';
    use MultilingualMarkdown\Lexer;

    /**
     * Debugging version of Lexer class.
     * Sends a separator to standard output after a set of tokens is output.
     */
    class DebugLexer extends Lexer
    {
        private $dashes;
        public function __construct(PicturesMgr $pm)
        {
            parent::__construct($pm);
            $this->dashes = str_repeat('-', 60);
        }

        public function output(Filer &$filer)
        {
            echo "$this->dashes\n";
            echo "OUTPUT\n";
            echo "$this->dashes\n";
            $safeTrace = $this->outputTrace;
            $this->outputTrace = true;
            $result = parent::output($filer);
            $this->outputTrace = $safeTrace;
            echo "$this->dashes\n";
            return $result;
        }
    }
}