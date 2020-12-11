<?php

/**
* Multilingual Markdown generator - Numbering class
* The class handles numbering schemes interpretation and automatic increment of heading levels numbers.
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
* @package   mlmd_numbering_class
* @author    Francis Piérot <fpierot@free.fr>
* @copyright 2020 Francis Piérot
* @license   https://opensource.org/licenses/mit-license.php MIT License
* @link      TODO
*/

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'OutputModes.class.php';

    /**
     * Numbering class, used by $allNumberings array for all files.
     */
    class Numbering
    {
        // settings

        /** Output mode */
        private $outputMode = OutputModes::MD;
        /** starting level, do not number levels below */
        private $startLevel = 10;
        /** ending level, do not number levels above */
        private $endLevel = 0;
        /** level => prefix for this level alone, should only be used for level 1 */
        private $levelsPrefix = [];
        /** level => starting symbol ('a'..'z', 'A'..'Z', '1'..'9, '&I', '&i') */
        private $levelsNumbering = [];
        /** level => boolean for Roman numbers */
        private $levelsRoman = [];
        /** level => separator string after symbol for each level */
        private $levelsSeparator = [];

        // status

        /** current increment for each level, -1 to disable*/
        private $curLevelNumbering = [];
        /** previous level processed by getTOCline() */
        private $prevLevel = 0;

        // Roman numbers arrays
        private static $intToRoman = [1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD', 100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL', 10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'];
        private static $romanToInt = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];

        /**
         * Return Roman number from decimal.
         *
         * @param int $number the number to translate
         *
         * @return string the corresponding Roman number
         */
        public static function getRoman(int $number): string
        {
            $result = '';
            foreach (self::$intToRoman as $limit => $roman) {
                while ($number >= $limit) {
                    $number -= $limit;
                    $result .= $roman;
                }
                if ($number == 0) {
                    break;
                }
            }
            return $result;
        }

        /**
         * Return decimal from Roman number.
         * The given string is interpreted as a Roman number until a non Roman notation is met,
         * or until the end of string. If the string is no Roman number, no error happens and the
         * value 0 is returned.
         *
         * @param string $roman the Roman number to translate
         *
         * @return int the integer value, until first non Roman character or end of $roman
         */
        public static function getFromRoman(string $roman): int
        {
            $result = 0;
            $maxpos = mb_strlen($roman) - 1;
            $pos = 0;
            while ($pos < $maxpos) {
                // try 2 characters
                $test = mb_substr($roman, $pos, 2);
                if (\array_key_exists($test, self::$romanToInt)) {
                    $result += self::$romanToInt[$test];
                    $pos += 2;
                } else {
                    // try 1st character
                    $test = $test[0];
                    if (\array_key_exists($test, self::$romanToInt)) {
                        $result += self::$romanToInt[$test];
                        $pos += 1;
                    } else {
                        break;
                    }
                }
            }
            return $result;
        }

        /**
         * Set output mode.
         * If numbering scheme has been set, the output mode will use a numbered format.
         * If not, it will use a non-numbered format.
         * Setting a numbering scheme after setting the output mode will adjust the mode.
         *
         * @param string $name the output mode name 'md', 'mdpure', 'html' or 'htmlold'
         * @param object $logger the caller object with an error() function
         */
        public function setOutputMode(string $name, ?object $logger = null): void
        {
            $mode = OutputModes::getFromName($name, $this);
            if ($mode == OutputModes::INVALID) {
                if ($logger) {
                    $logger->error("invalid output mode name '$name'");
                }
                return;
            }
            $this->outputMode = $mode;
        }

        /**
         * Get output mode.
         */
        public function getOutputMode(): int
        {
            return $this->outputMode;
        }

        /**
         * Check if there is an active numbering scheme.
         *
         * @return bool true if a Numbering scheme is active, false if it is empty.
         */
        public function isActive(): bool
        {
            return count($this->levelsNumbering) > 0;
        }

        /**
         * Restrict a level between 1 and 9.
         *
         * @param int $level the proposed level
         *
         * @return int the level adjusted to fit between 1 and 9
         */
        public function checkLevel(int $level): int
        {
            return ($level < 1) ? 1 : (($level > 9) ? 9 : $level);
        }

        /**
         * Build the numbering scheme.
         *
         * A numbering scheme is made of one or more definitions, each definition sets a scheme
         * for a heading level.
         *
         * Syntax for string: <level><defsep>[<prefix>]<defsep><symbol><defsep><separator>[,...]
         *
         * <level>      1 to 9
         * <defsep>     any non-numeric character (e.g. ':' or '/'), will
         *              be used to separate parts in definition.
         * <prefix>     optional string of any characters except the <defsep> character
         * <symbol>     starting character, symbol or number from the following list:
         *              - `A` to `Z`: uppercase starting letter
         *              - `a` to `z`: lowercase starting letter
         *              - `&I`: uppercase Roman numbers (I, II, III etc), always start at 'I'
         *              - `&i`: lowercase Roman numbers (i, ii, iii etc), always start at 'i'
         *              - `1` to `9`: starting number
         * <separator>  any character string except comma ',' character
         *
         * @param string $scheme  the scheme string
         * @param object $logger  the caller object with an error() function
         * @see Logger interface
         */
        public function __construct(string $scheme, ?object $logger = null)
        {
            $this->levelsPrefix = []; // only allowed for level 1
            $this->levelsNumbering = [];
            $this->levelsRoman = [];
            $this->levelsSeparator = [];
            $this->curLevelNumbering = [];
            $this->definitionSeparator = ':';
            $schemeLen = mb_strlen($scheme);
            if ($schemeLen == 0) {
                return ;
            }

            // catch the <defsep> character after first level
            for ($pos = 0; $pos < $schemeLen; $pos += 1) {
                $c = mb_substr($scheme, $pos, 1);
                if (!is_numeric($c)) {
                    $this->definitionSeparator = $c;
                    break;
                }
            }
            // get all definitions and interpret each one
            $defs = explode(',', $scheme);
            $level = 0;
            $minLevel = 10;
            $maxLevel = 0;
            foreach ($defs as $def) {
                $parts = explode($this->definitionSeparator, $def);
                /*if (count($parts) < 3) {
                    if ($logger) {
                        $logger->error("invalid .numbering scheme '$def': ignored");
                    }
                    continue;
                }*/
                $prevLevel = $level;
                $level = $parts[0] ?? ($prevLevel + 1);
                if ((int)$level < $this->startLevel) {
                    $this->startLevel = $level;
                } elseif ((int)$level > $this->endLevel) {
                    $this->endLevel = $level;
                }
                $prefix = $parts[1] ?? '';
                $symbol = $parts[2] ?? '1';
                $separator = $parts[3] ?? '';
                if ($level < '1' || $level > '9') {
                    if ($logger) {
                        $logger->error("invalid .numbering scheme '$def': level must be between 1 and 9");
                    }
                    continue;
                }
                /// store level parameters
                if ($level > 1 && !empty($prefix)) {
                    if ($logger) {
                        $logger->warning("prefix '$prefix' in '$def' will be ignored (level > 1)");
                    }
                    $this->levelsPrefix[$level] = '';
                } else {
                    $this->levelsPrefix[$level] = $prefix;
                }
                $this->levelsRoman[$level] = (($symbol == '&I') || ($symbol == '&i'));
                if (
                    ($symbol < '1' || $symbol > '9')
                    && ($symbol < 'a' || $symbol > 'z')
                    && ($symbol < 'A' || $symbol > 'Z')
                    && (!$this->levelsRoman[$level])
                ) {
                    if ($logger) {
                        $logger->error("invalid numbering symbol in .numbering '$def': values are 1 to 9, 'a' to 'z', 'A' to 'Z', '&i' or '&I'");
                    }
                } else {
                    $this->levelsNumbering[$level] = $symbol;
                }
                $this->levelsSeparator[$level] = $separator;
                $this->curLevelNumbering[$level] = 0; // equ to setLevelNumber($level,1))
            }
            // sort all by level so foreach() can track levels in growing order
            ksort($this->levelsPrefix, SORT_NUMERIC);
            ksort($this->levelsNumbering, SORT_NUMERIC);
            ksort($this->levelsRoman, SORT_NUMERIC);
            ksort($this->levelsSeparator, SORT_NUMERIC);
            ksort($this->curLevelNumbering, SORT_NUMERIC);
        }

        /**
         * Set the first and last level to number.
         * Zeroing both limits (calling with no parameters) disables Numbering.
         * Numbering scheme is not required to define all levels
         * between start and end.
         *
         * @param int $start first level to number between 1 and 9, 0 to disable
         * @param int $end   last level to number between start and 9, 0 to disable
         */
        public function setLevelLimits(int $startLevel = 0, int $endLevel = 0): void
        {
            $this->startLevel = $startLevel;
            $this->endLevel = $endLevel;
        }

        /**
         * Starting TOC heading level accessor.
         */
        public function getStart()
        {
            return $this->startLevel;
        }
        /**
         * Ending TOC heading level accessor.
         */
        public function getEnd()
        {
            return $this->endLevel;
        }

        /**
         * Level current number accessor.
         */
        public function getLevelNumbering(int $level): int
        {
            return $this->curLevelNumbering[$level];
        }

        /**
         * Reset all numbers on all levels except top level
         */
        public function resetSubNumbering(): void
        {
            foreach ($this->curLevelNumbering as $index => &$curNumbering) {
                if ($index != 1) {
                    $curNumbering = 0;
                }
            }
            $this->prevLevel = 0;
        }

        /** Set the number of one level.
         *
         * @param int $level  the level to modify: 1 for '#' headings, 2 for '##' etc
         * @param int $number the starting number to set : 1 for first value in the level scheme, 2 for second value etc
         */
        public function setLevelNumber(int $level, int $number): void
        {
            $this->curLevelNumbering[$level] = $number - 1;
        }

        

        /**
         * Get the numbering sequence for a level and update numbers.
         * The number depends on current numbers at each level and
         * getting the numbering will update the number for the relevant levels:
         *
         * - if previous level was above (level > previous), new level starts at 0.
         * - else the number for the level is incremented
         *
         * Then the string using all levels symbols and numbers from level start to requested
         * level is computed.
         *
         * A dash is added as prefix for some output modes if requested (for TOC lines)
         *
         * HTMLNUM/HTMLOLDNUM:  `<numbering>)`
         * MDPURE with NUM:     `1.`
         * MDNUM:               `- <numbering>)`
         * HTML/HTMLOLD/MD:     `-`
         *
         * @param int  $level   the level
         * @param bool $addDash true to add a dash prefix in MDNUM or non numbered modes
         *
         * @return string the numbering string to use on headings and TOC lines
         */
        public function getText(int $level, bool $addDash): string
        {
            $sequence = '';
            if ($addDash && in_array($this->outputMode, [OutputModes::MDNUM,OutputModes::MD,OutputModes::HTML,OutputModes::HTMLOLD])) {
                $sequence = '- ';
            }
            if (($this->startLevel == 0 && $this->endLevel == 0) || (count($this->levelsNumbering) == 0)) {
                return $sequence;
            }

            // adjust number for this level
            $this->nextNumber($level);

            // MD pure output mode : all numberings can be '1.' followed by a single space, and Markdown
            // viewers will figure out actual numbering.
            // MLMD give the actual level number (1. 2. 3. etc) , although not necessary.
            if ($this->outputMode == OutputModes::MDPURE) {
                $number = $this->curLevelNumbering[$level] + 1; // 0 becomes '1.', 1 becomes '2.' etc
                if ($number > 0) {
                    $sequence = "{$number}. ";
                }
                return $sequence;
            }

            // Only level 1 headings may be prefixed (Chapter I, etc)
            if (($level == 1) && isset($this->levelsPrefix[1]) && ($this->startLevel <= 1) && ($this->curLevelNumbering[1] >= 0)) {
                $sequence .= $this->levelsPrefix[1];
            }

            // build <symbol><separator>... string
            for ($i = $this->startLevel; $i <= $level; $i += 1) {
                if ($this->curLevelNumbering[$i] < 0) {
                    continue;
                }
                // set <symbol><separator>
                $numbering = $this->levelsNumbering[$i] ?? '1';
                if (is_numeric($numbering)) {
                    $sequence .= $numbering + $this->curLevelNumbering[$i];
                } else {
                    if ($this->levelsRoman[$i]) {
                        $roman = self::getRoman($this->curLevelNumbering[$i] + 1);// I for number 0
                        if ($this->levelsNumbering[$i] == "&i") {
                            $roman = \strtolower($roman);
                        }
                        $sequence .= $roman;
                    } else {
                        $sequence .= chr(ord($numbering) + $this->curLevelNumbering[$i]);
                    }
                }
                // add separator if not last level, else ') '
                if ($i < $level) {
                    $sequence .= $this->levelsSeparator[$i] ?? '';
                } else {
                    $sequence .= ') ';
                }
            }
            return $sequence;
        }

        /**
         * Go to next numbering for a level and update numbers.
         * The number depends on current numbers at each level and
         * getting the numbering will update the number for the relevant levels:
         *
         * - if level is 1 ('#' heading) the number doesn't change - it is uniquely set for each file
         * - if previous level was above (level > previous), new level starts at number 0.
         * - else the number for the level is incremented
         *
         * @param int  $level   the level, starting at 1 for top '#' heading
         *
         * @return nothing
         */
        public function nextNumber(int $level): void
        {
            if ($this->startLevel == 0 && $this->endLevel == 0) {
                return;
            }
            if ($level == 1) {
                return;
            }
            // adjust number for this level
            if ($level <= $this->prevLevel) {
                $this->curLevelNumbering[$level] += 1;
            } else {
                $this->curLevelNumbering[$level] = 0;
            }
            $this->prevLevel = $level;
        }
    }
}
