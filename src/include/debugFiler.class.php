<?php

/**
 * Multilingual Markdown generator - DEBUG Filer class
 *
 * Adds debug output to parent filer normal class.
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
 * @package   mlmd_debug_filer_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'Filer.class.php';
    use MultilingualMarkdown\Logger;
    use MultilingualMarkdown\languageList;
    use MultilingualMarkdown\Filer;
    
    /**
     * Debugging version of Filer class.
     * Each character read is sent to standard output.
     */
    class DebugFiler extends Filer
    {
        private $currentDisplayed = false;

        public function getCurrentChar(): ?string
        {
            $c = parent::getCurrentChar();
            if (!$this->currentDisplayed) {
                echo $c;
                $this->currentDisplayed = true;
            }
            return $c;
        }
        public function getNextChar(): ?string
        {
            $c = parent::getNextChar();
            if ($c !== null) {
                echo $c;
            } else {
                echo "<null>\n";
            }
            return $c;
        }
        public function getString(int $charsNumber): ?string
        {
            $s = parent::getString($charsNumber);
            if ($s !== null) {
                $c = $this->getCurrentChar();
                if ($this->currentDisplayed) {
                    echo mb_substr($s, 1), $c;
                } else {
                    echo $s, $c;
                }
            } else {
                echo "<null>\n";
            }
            return $s;
        }
    }
}
