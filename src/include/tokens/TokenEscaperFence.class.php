<?php

/**
 * Multilingual Markdown generator - TokenEscaperFence class
 *
 * This class represents a token for code fence <```> start or end at the beginning of a line
 * (or after spaces only) present before and after escaped text lines.
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
 * @package   mlmd_token_escaper_fence_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'TokenBaseEscaper.class.php';

    use MultilingualMarkdown\TokenBaseEscaper;
    
    /**
     * Class for the code fence.
     * The code fence opening starts with a triple back-tick possibly followed
     * by a language name. The token will skip over the rest of current line and
     * send everything to outputs while watching for ending fence.
     */
    class TokenEscaperFence extends TokenBaseEscaper
    {
        public function __construct()
        {
            parent::__construct('```');
        }
        /**
         * Identify self against an UTF-8 buffer and position.
         * Make sure code fence starts a new line, tolerating leading indentation
         * (spaces/tabs only) so fences nested under list items are still recognized.
         */
        public function identifyInBuffer(?string $buffer, int $pos): bool
        {
            if ($buffer != null) {
                if ($pos > 0) {
                    $before = mb_substr($buffer, 0, $pos);
                    $lastEol = mb_strrpos($before, "\n");
                    $indent = ($lastEol === false) ? $before : mb_substr($before, $lastEol + 1);
                    if (trim($indent, " \t") !== '') {
                        return false;
                    }
                }
                return parent::identifyInBuffer($buffer, $pos);
            }
            return false;
        }
        /**
         * Let the token self-identify against an input Filer or Storage object.
         * Tolerates leading indentation (spaces/tabs only) before the fence marker,
         * so fences nested under list items are still recognized as fences.
         *
         * @param object $input the Filer or Storage object
         *
         * @return bool true if the current token can be found at current Filer position and buffer content.
         */
        public function identify(object $input): bool
        {
            $prevChars = $input->fetchPreviousChars(32);
            if ($prevChars !== null && $prevChars !== '') {
                $lastEol = mb_strrpos($prevChars, "\n");
                $indent = ($lastEol === false) ? $prevChars : mb_substr($prevChars, $lastEol + 1);
                if (trim($indent, " \t") !== '') {
                    return false;
                }
            }
            return parent::identify($input);
        }

        /**
         * Process input: get text until we find the closing escape marker.
         * Code fenced text can cross many lines so don't stop at end of input if
         * it's a Storage and read next lines from filer into input.
         * Update tokens array with the token itself. The escaped text is stored
         * by the token.
         */
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $this->content = $input->getLine(); // <```code> starting marker
            $firstLine = $filer->getCurrentLineNumber(); // remember opening line for error reporting
            do {
                $thisLine = $filer->getLine();
                if ($thisLine === null) {
                    // EOF reached before a closing fence: getLine() would keep returning null forever
                    // and identifyInBuffer(null, 0) is always false, so without this guard the loop
                    // below never terminates. Report and stop instead of spinning.
                    $filer->error('Code fence (```) unable to find closing code fence', $filer->current(), $firstLine);
                    break;
                }
                $this->content .= "\n" . $thisLine;
                // allow the closing fence to be indented like the opening one: skip
                // leading spaces/tabs before checking for the closing marker.
                $closingPos = mb_strlen($thisLine) - mb_strlen(ltrim($thisLine, " \t"));
            } while (!$this->identifyInBuffer($thisLine, $closingPos));
            // In the lines below I take care to modify $this->content before
            // appending $this to lexer, but it may be possible to add the token first
            // and then modify its content. As PHP is not clear about about object
            // copies I prefer to do as if $this could be copied in the append
            // and not stored by reference.

            // replace the last EOLs by one EOL token?
            $this->content = rtrim($this->content, "\n");
            $this->length = mb_strlen($this->content);
            $lexer->appendToken($this, $filer);
        }
        public function output(Lexer &$lexer, Filer &$filer): bool
        {
            return $filer->output($this->content, false, $this->type, $lexer->isOutputTraced());
        }
    }

}
