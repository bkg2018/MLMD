<?php

/**
 * Static class for output modes names and Constants.
 * An output mode defines the style for heading anchor, heading spacing prefix, numbering and TOC links.
 * This file must be included in classes using an output mode.
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
 * @package   mlmd_outputmode_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    class OutputModes
    {
        public const INVALID    = -1;
        public const MD         = 0;
        public const MDNUM      = self::MD + 1;
        public const MDPURE     = self::MDNUM + 1;
        public const HTML       = self::MDPURE + 1;
        public const HTMLNUM    = self::HTML + 1;
        public const HTMLOLD    = self::HTMLNUM + 1;
        public const HTMLOLDNUM = self::HTMLOLD + 1;

        private static $modeName = [
            // mode -> mode name
            self::MD            => 'md',
            self::MDNUM         => 'md',
            self::MDPURE        => 'mdpure',
            self::HTML          => 'html',
            self::HTMLNUM       => 'html',
            self::HTMLOLD       => 'htmlold',
            self::HTMLOLDNUM    => 'htmlold'
        ];
        private static $numberedMode = [
            // mode name => mode when numbered
            'md'        => self::MDNUM,
            'mdpure'    => self::MDPURE, // numbering is actually ignored by mdpure
            'html'      => self::HTMLNUM,
            'htmlold'   => self::HTMLOLDNUM
        ];
        private static $nonNumberedMode = [
            // mode name => mode when not numbered
            'md'        => self::MD,
            'mdpure'    => self::MDPURE,
            'html'      => self::HTML,
            'htmlold'   => self::HTMLOLD
        ];
        
        /**
         * Check if a mode name is valid
         */
        public static function isValid(string $name): bool
        {
            return \array_key_exists($name, self::$nonNumberedMode);
        }

        /**
         * Get the mode constant for a mode name and a numbering scheme.
         *
         * @param string $name the mode name
         * @param object $numbering the Numbering object, can be omitted
         *
         * @return int the mode constant, or OutputModes::INVALID (-1) if invalid name.
         */
        public static function getFromName(string $name, Numbering &$numbering = null): int
        {
            $arrayName = $numbering ? ($numbering->isActive() ? 'numberedMode' : 'nonNumberedMode') : 'nonNumberedMode';
            if (\array_key_exists($name, self::$$arrayName)) {
                return self::$$arrayName[$name];
            }
            return self::INVALID;
        }

        /**
         * Get the name for a mode constant.
         *
         * @param int $mode the mode constant
         *
         * @return string the mode name, null if invalid constant.
         */
        public static function getName(int $mode): string
        {
            if (\array_key_exists($mode, self::$modeName)) {
                return self::$modeName[$mode];
            }
            return null;
        }

        /**
         * Return a named anchor or null if unknown.
         */
        public static function getAnchor(int $mode, string $name): ?string
        {
            if (\is_numeric($name)) {
                $name = "a$name";
            }
            switch ($mode) {
                case OutputModes::MDPURE:
                    return "{#$name}";
                case OutputModes::HTMLOLD:
                case OutputModes::HTMLOLDNUM:
                    return "<A name=\"$name\"></A>";
                default:
                    return "<A id=\"$name\"></A>";
            }
            return null;
        }
    }
}
