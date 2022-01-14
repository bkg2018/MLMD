<?php

/**
 * Multilingual Markdown generator - Storage class
 *
 * The Storage class handles buffers for reading text from an input file. The filenames and pathes are not
 * managed by Storage, which uses a file handle sent by the caller. The file is neither opened
 * nor closed by Storage but the current file positions are modified by Storage when reading, so callers
 * should not rely on positions nor alter them unless explicitly expected. The Storage class
 * gives functions to advance in input file and check previous or next characters without
 * moving the current position.
 *
 * See the Filer class for file names handling and files opening.
 *
 * Ends of lines are normalized to a single \n (0x0A ASCII) character alone with no \r (0x0D ASCII).
 *
 * NOTATION:
 *      - EOL means end of line and/or the end-of-line control character (\n)
 *      - EOF means end of file.
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
 * @package   mlmd_storage_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    class Storage
    {
        /** current line content */
        private $buffer = '';
        /** current pos in line buffer (utf-8) */
        private $bufferPosition = 0;
        /** current line size in characters (utf-8) */
        private $bufferLength = 0;
        /** current line number from input file */
        private $curLine = 0;
        /** current last line number stored in buffer on or after current position */
        private $lastLine = 0;
        /** array of last 3 characters: [0] = current, [1] = previous, [2] = pre-previous */
        private $previousChars = [];
        /** file opened for reading (setInputFile) or null */
        private $inFile = null;

        public function __construct($source)
        {
            if (\is_resource($source)) {
                $this->setInputFile($source);
            } elseif (is_string($source)) {
                $this->setInputBuffer($source);
            }
        }

        /**
         * Set an input file handle for further reading.
         * This function releases any previous file and resets currents input buffer and status.
         *
         * @param resource $file the file handle for reading. Position is set to the beginning of file.
         *
         * @return bool true if the file and first paragraph is ready.
         */
        public function setInputFile($file): bool
        {
            if (!\is_resource($file)) {
                return false;
            }
            if (isset($this->inFile)) {
                unset($this->inFile);
            }
            $this->inFile = $file;
            \rewind($file);
            if (isset($this->buffer)) {
                unset($this->buffer);
                $this->buffer = '';
            };
            $this->bufferLength = 0;
            $this->bufferPosition = 0;
            // initialize on first line
            $this->curLine = 1;
            $this->fetchCharacters(1);
            $this->previousChars[0] = mb_substr($this->buffer, 0, 1);
            return true;
        }

        /**
         * Close input file and empty buffer.
         */
        public function close(): void
        {
            if (\is_resource($this->inFile)) {
                unset($this->inFile);
                $this->inFile = null;
            }
            if (isset($this->buffer)) {
                unset($this->buffer);
                $this->buffer = '';
                $this->bufferLength = 0;
                $this->bufferPosition = 0;
            };
        }

        /**
         * Set the buffer content and length.
         * Ignores any opened file and totally replace the buffer content.
         */
        public function setInputBuffer(?string $content): void
        {
            if (isset($this->buffer)) {
                unset($this->buffer);
            }
            $this->buffer = $content;
            $this->bufferLength = mb_strlen($content);
            $this->bufferPosition = 0;
            if ($this->bufferLength > 0) {
                // remember first character and simulate a previous EOL
                $this->previousChars = [mb_substr($this->buffer, 0, 1), "\n"];
            }
        }

        /**
         * Read a line from current file and delete ending EOL character then return
         * resulting string. Return null when already at end of file.
         */
        public function loadLine(): ?string
        {
            if (!isset($this->inFile)) {
                return null;
            }
            $line = fgets($this->inFile);
            if ($line === false) {
                return null;
            }
            return rtrim($line, "\n\r");
        }

        /**
         * Ensure a number of characters are available in buffer from input file.
         * Content is read line by line until wanted length is reached.
         * Windows CR are deleted from input, and the EOL character is moved
         * to the starting of line. Current char and position do not change.
         * The function just ensure that enough characters are present in buffer
         * and does not return any string.
         *
         * @param int $length number of character to make available in buffer
         */
        public function fetchCharacters(int $length): void
        {
            // are there enough characters?
            if ($this->bufferPosition + $length < $this->bufferLength) {
                return ;
            }
            // get next line cleaned of EOL
            $line = $this->loadLine();
            if ($line === null) {
                return;
            }
            // add to buffer with an EOL
            $this->buffer .= $line . "\n";
            $this->bufferLength += (1 + mb_strlen($line));
            // keep buffer size at about 4KB when position is above 1KB
            if ($this->bufferLength > 4096 && $this->bufferPosition > 1024) {
                $this->buffer = mb_substr($this->buffer, 1024);
                $this->bufferPosition -= 1024;
                $this->bufferLength = mb_strlen($this->buffer);
            }
        }

        /**
         * Get the current line number for current reading position.
         */
        public function getCurrentLineNumber()
        {
            return $this->curLine;
        }

        /**
         * Return the current UTF-8 character from current paragraph.
         * Load next paragraph if no paragraph is loaded yet.
         *
         * @return null|string current character ('\n' for EOL),  null when file and buffer are finished.
         */
        public function getCurrentChar(): ?string
        {
            if (!$this->buffer) {
                return null;
            }
            // immediate return if ready
            if ($this->bufferPosition < $this->bufferLength) {
                return $this->previousChars[0];
            }
            // need to append at least 1 char from input
            $this->fetchCharacters(1);
            // check again, length didn't change if we're at EOF
            if ($this->bufferPosition < $this->bufferLength) {
                $this->previousChars[0] = mb_substr($this->buffer, $this->bufferPosition, 1);
                return $this->previousChars[0];
            }
            return null;
        }

        /**
         * Return the previous UTF-8 character .
         *
         * @return null|string previous character ('\n' for EOL).
         */
        public function getPrevChar(): ?string
        {
            return $this->previousChars[1] ?? null;
        }

        /**
         * Return the next UTF-8 character from current buffer, return null if end of file.
         *
         * @return null|string new current character ('\n' for EOL),  null when file and buffer are finished.
         */
        public function getNextChar(): ?string
        {
            // ensure next character
            $this->fetchCharacters(1);
            $this->bufferPosition += 1;
            // EOF?
            if ($this->bufferPosition >= $this->bufferLength) {
                $c = null;
                $this->bufferPosition = $this->bufferLength;
            } else {
                $c = mb_substr($this->buffer, $this->bufferPosition, 1);
            }
            // adjust previous characters array (0 = current, 1 = previous, 2 = pre-previous)
            if (count($this->previousChars) > 2) {
                array_pop($this->previousChars);        // unset [2]
            }
            array_unshift($this->previousChars, $c);    // insert [0], pushes 0=>1 to 1=>2
            // return new current character
            return $this->previousChars[0];
        }

        /**
         * Read and return the text from starting  position and until end of line. Do not include
         * the end of line character in the returned text.
         *
         * - if already at eof, returns null.
         * - if an empty line, return empty string
         * - else return the line content until the next EOL (which is the next line start)
         *
         * If there is an eol character it is read but not returned by the function. The caller
         * must assume that an end of line character is present for any non null return.
         */
        public function getLine(): ?string
        {
            $text = '';
            $this->adjustNextLine();
            $char = $this->getCurrentChar();
            if ($char === null) {
                return null;
            }
            while (($char != "\n") && ($char !== null)) {
                $text .= $char;
                $char = $this->getNextChar();
            }
            return $text;
        }

        /**
         * Skip every character starting at next one until next line starts. Do not read the first character on new line,
         * so at exit the current character is the current line EOL.
         *
         * @return null|string EOL when line has been read, null at end of file
         */
        public function gotoNextLine(): ?string
        {
            // adjust line number and read position if current character is EOL.
            $this->adjustNextLine();
            // check for end of file
            $char = $this->getNextChar();
            if ($char == null) {
                return null;
            }
            // read until the next EOL
            while (($char !== null) && ($char != "\n")) {
                $char = $this->getNextChar();
            }
            // return null for EOF, else EOL
            return $char;
        }

        /**
         * Go next line and adjust line number if current character is EOL.
         * Return true if line has been adjusted, false if nothing done.
         */
        public function adjustNextLine(): bool
        {
            if ($this->getCurrentChar() == "\n") {
                $this->curLine += 1;
                $this->getNextChar();
                return true;
            }
            return false;
        }

        /**
         * Read a string with a number of characters starting with the current one.
         * Return null if already at end of file. The final current position is set
         * on the first character past the string.
         */
        public function getString(int $charsNumber): ?string
        {
            // skip current end of line if appropriate
            $this->adjustNextLine();
            $result = $this->getCurrentChar();
            $char = $this->getNextChar();
            // append chars [1..N-1]
            for ($i = 1; ($i < $charsNumber) && ($char != null); $i += 1) {
                $result .= $char;
                if ($this->adjustNextLine()) {
                    $char = $this->getCurrentChar();
                } else {
                    $char = $this->getNextChar();
                }
            }
            return $result;
        }

        /**
         * Look at previous UTF-8 characters.
         * Cannot read more than further the beginning of file or the beginning
         * of current buffer position. The buffer is at most up to 3072 characters before current
         * position so it is safe to request for a lot of previous characters up to this limit
         * but at the beginning the buffer will only have as much as the 4096 first
         * characters of file.
         *
         * @param int $charsNumber the number of previous characters to fetch
         *
         * @return null|string     the characters before current position.
         */
        public function fetchPreviousChars(int $charsNumber): ?string
        {
            $startPosition = max(0, $this->bufferPosition - $charsNumber);
            $length = min($charsNumber, $this->bufferPosition);
            if ($length <= 0) {
                return null;
            }
            return mb_substr($this->buffer, $startPosition, $length);
        }

        /**
         * Return next UTF-8 characters from current buffer, return null if end of file.
         * Do not advance reading position, just send back future characters to read.
         * If the requested number of characters is not available, return what's left.
         *
         * @param int $charsNumber the number of characters to return
         * @return null|string     the next characters which will be read,  null when file and buffer are finished.
         */
        public function fetchNextCharacters(int $charsNumber): ?string
        {
            if ($this->bufferPosition >= $this->bufferLength) {
                return null;
            }
            $this->fetchCharacters($charsNumber);
            $nextPosition = $this->bufferPosition + 1;
            if ($nextPosition >= $this->bufferLength) {
                return null; // end of buffer/file already reached
            }
            return mb_substr($this->buffer, $nextPosition, $charsNumber);
        }

        /**
         * Check if a given string with given length matches incoming input.
         *
         * @param string $word   the word to check
         * @param int    $length the number of UTF-8 characters in word
         *
         * @return bool true if the word matches incoming input
         */
        public function isMatchingWord(string &$word, int $length): bool
        {
            $content  = $this->previousChars[0] . $this->fetchNextCharacters($length - 1);
            return (mb_strcmp($content, $word) == 0);
        }

        /**
         * Check if a string from an array matches incoming input.
         * The arrays must be indexed by successive numbers starting at 0
         *
         * @param string[] $allWords the array of words to check
         *
         * @return int index of the word found, or -1 if none found
         */
        public function isMatchingWords(array &$allWords, array &$allLengths): int
        {
            foreach ($allWords as $index => &$word) {
                if ($this->isMatchingWord($word, $allLengths[$index])) {
                    return $index;
                }
            }
            return -1;
        }
    }

    /*// little test
    $storage = new Storage();
    $file = fopen('testdata/test.mlmd', 'rt');
    $storage->setInputFile($file);
    do {
        $line = $storage->getLine();
        if ($line === null) break;
        echo $storage->getCurrentLineNumber(), ':', $line, "\n";
    } while (1);
    \fclose($file);*/
}
