<?php

/**
 * Multilingual Markdown generator - Language List class
 *
 * A Language list allow management of a set of language codes and ISO codes.
 * They are stored as $index => [$code => $iso] in the $allLanguages array
 * where $index is an integer value.
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
 * @package   mlmd_languagelist_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'Constants.php';

    class LanguageList implements \SeekableIterator, \ArrayAccess, \Countable
    {
        private $allLanguages = [];         // array of languages: [i => ['code' => code value, 'iso' => iso value]]
        private $curIndex = 0;
        private $mainLanguage = null;       // main language code
        private $lowerMainLanguage = null;  // main language code in lowercases


        // Seekable Iterator interface
        public function current(): mixed
        {
            if ($this->curIndex < count($this->allLanguages)) {
                return $this->allLanguages[$this->curIndex];
            }
            \trigger_error("Invalid current index in language list", E_USER_ERROR);
        }
        public function key(): mixed
        {
            return $this->curIndex;
        }
        public function next(): void
        {
            $this->curIndex += 1;
        }
        public function rewind(): void
        {
            $this->curIndex = 0;
        }
        public function valid(): bool
        {
            if ($this->curIndex < count($this->allLanguages)) {
                return isset($this->allLanguages[$this->curIndex]);
            }
            return false;
        }
        public function seek($position): void
        {
            if (\array_key_exists($position, $this->allLanguages)) {
                $this->curIndex = $position;
            } else {
                \trigger_error("Invalid position $position in language list", E_USER_ERROR);
            }
        }
        // ArrayAccess interface
        public function offsetExists($index): bool
        {
            return isset($this->allLanguages[$index]);
        }
        public function offsetGet($index): mixed
        {
            return $this->allLanguages[$index];
        }
        public function offsetSet($index, $value): void
        {
            if ($index !== null) {
                $this->allLanguages[$index] = $value;
            } else {
                $this->allLanguages[] = $value;
            }
        }
        public function offsetUnset($index): void
        {
            unset($this->allLanguages[$index]);
            array_splice($this->allLanguages, $index, 1);
        }
        // Coutable interface
        public function count(): int
        {
            return count($this->allLanguages);
        }

        /**
         * Return code and ISO for a given language or null if it is not known.
         * Each language code is stored as an array where index 'code' gives the code
         * declared in the .languages directive, and 'iso' gives the optional ISO code
         * also declared in theb .languages directive. This seconde ISO code can be used
         * with various web services URLs for instance to point to a flag picture. The
         * {iso} variable expands to this code.
         *
         * @param string $code a code previously given in .languages directive
         *
         * @return array the values array for the language, or null if the language code is unknown.
         */
        public function getLanguage(string $code): ?array
        {
            if (\in_array($code, [ALL, IGNORE, DEFLT])) {
                return ['code' => $code];
            }
            foreach ($this->allLanguages as $index => $array) {
                if (isset($array['code']) && ($array['code'] == $code)) {
                    return $array;
                }
            }
            return null;
        }

        /**
         * Check if a language code is valid.
         * This includes special codes like 'all', 'ignore' and 'default', plus each
         * language added by the .languages directives.
         */
        public function existLanguage(string $code): bool
        {
            if (\in_array($code, [ALL, IGNORE, DEFLT])) {
                return true;
            }
            foreach ($this->allLanguages as $array) {
                if (isset($array['code']) && ($array['code'] == $code)) {
                    return true;
                }
            }
            return false;
        }
        
        /**
         * Tell if a language code is main language.
         * The main language has the effect of not putting the code in the output file extension,
         * so a README.mlmd template will be converted into README.md for the main language.
         */
        public function isMain(string $code): bool
        {
            return mb_strtolower($code) == $this->lowerMainLanguage;
        }

        /**
         * Sets the main language code.
         * If the code has not been added in language list the function returns an error.
         */
        public function setMain(string $main)
        {
            $this->mainLanguage = null;
            foreach ($this->allLanguages as $index => $array) {
                if ($array['code'] == $main) {
                    $this->mainLanguage = $main;
                    $this->lowerMainLanguage = \mb_strtolower($main);
                    return true;
                }
            }
            return false;
        }

        /**
         * Set the list from a parameter string.
         * The string format is a set of assignations <code>=<value> separated by a comma and optional spaces.
         * Each assignation can be one of the two following formats:
         * - <code>[=<isocode>]  : the <code> will be stored, optionally associated with the given ISO code
         * - main=<code>         : the <code> will be set as the main language
         * The main language has the effect of not putting the code in the output file extension,
         * so a README.mlmd template will be converted into README.md for the main language while
         * other languages will go into README.<code>.md files.
         *
         * @param string $string the parameter string as described above.
         *
         * @return bool true if the languages have been set correctly.
         */
        public function setFrom(?string $string): bool
        {
            if ($string == null) {
                return false;
            }
            // change spaces into a comma for easier explode()
            $string = str_replace(' ', ',', trim($string));
            $allAssignments = explode(',', $string);
            $main = null;
            $result = true;
            foreach ($allAssignments as $assignment) {
                $parts = explode('=', trim($assignment));
                if (count($parts) > 0) {
                    switch ($parts[0]) {
                        case 'main':
                            $main = $parts[1] ?? '';
                            break;
                        default:
                            $exists = $this->getLanguage($parts[0]);
                            if ($exists == null) {
                                $this[] = ['code' => $parts[0], 'iso' => $parts[1] ?? null];
                            }
                    }
                }
            }
            if ($main !== null) {
                $result = $this->setMain($main);
            }
            return $result;
        }
    }
}
