<?php

/**
 * Multilingual Markdown generator - Heading Array class
 * This class maintains an array which contains one Heading object for each heading
 * from one file. It can find a heading in this array from its line number.
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
 * @package   mlmd_heading_array_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    use MultilingualMarkdown\Heading;
    use MultilingualMarkdown\Numbering;
    
    require_once 'Heading.class.php';
    require_once 'Numbering.class.php';

    /**
     * Heading array class for all headings from a file.
     * The file name given at allocation is used in TOC links. It should
     * be relative to the path of the file where the link will be written.
     * It can be ignored when written in the origin file itself. (e.g. in local TOC.)
     */
    class HeadingArray implements \SeekableIterator, \ArrayAccess, \Countable
    {
        private $allHeadings = [];  // all headings from a file
        private $curIndex = 0;      // current exploration index
        private $outputMode = OutputModes::MD;
        private $file = '';         // path of file relative to root dir for these headings
        private $null = null;

        // Seekable Iterator interface
        public function current(): Heading
        {
            if ($this->curIndex < count($this->allHeadings)) {
                return $this->allHeadings[$this->curIndex];
            }
            \trigger_error("Invalid current index in headings array", E_USER_ERROR);
        }
        public function key(): int
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
            if ($this->curIndex < count($this->allHeadings)) {
                return isset($this->allHeadings[$this->curIndex]);
            }
            return false;
        }
        public function seek($position): void
        {
            if (\array_key_exists($position, $this->allHeadings)) {
                $this->curIndex = $position;
            } else {
                \trigger_error("Invalid position $position in heading array", E_USER_ERROR);
            }
        }
        // ArrayAccess interface
        public function offsetExists($index): bool
        {
            return isset($this->allHeadings[$index]);
        }
        public function offsetGet($index): mixed
        {
            return $this->allHeadings[$index];
        }
        public function offsetSet($index, $value): void
        {
            if ($index !== null) {
                $this->allHeadings[$index] = $value;
            } else {
                $this->allHeadings[] = $value;
            }
        }
        public function offsetUnset($index): void
        {
            unset($this->allHeadings[$index]);
            array_splice($this->allHeadings, $index, 1);
        }
        // Coutable interface
        public function count(): int
        {
            return count($this->allHeadings);
        }

        /**
         * Build the array, register the file base path (no extension)
         */
        public function __construct(string $file)
        {
            $this->file = $file;
        }

        /**
         * Set the output mode.
         * The output mode can be combined with a numbering scheme, in which case
         * the numbering scheme is reset to all 0 offsets and associated for the
         * given output mode. The scheme may have been set beforehand, but it can also be
         * set afterwards on the Numbering object.
         *
         * @param string    $name      the output mode name, 'md', 'mdpure', 'html' or 'htmlold'
         * @param Numbering $numbering the Numbering associated object if any, can be null or ignored
         */
        public function setOutputMode(string $name, ?Numbering $numbering = null): void
        {
            $this->outputMode = OutputModes::getFromName($name);
            if ($numbering !== null) {
                $numbering->setOutputMode($name);
                $numbering->resetSubNumbering();
            }
        }


        /**
         * Reset exploration to first heading.
         */
        public function resetCurrent(): void
        {
            $this->curIndex = 0;
        }

        /**
         * Get last indexx value for the array.
         *
         * @return int the last valid index value
         */
        public function getLastIndex()
        {
            return count($this->allHeadings) - 1;
        }

        /**
         * Access to current heading.
         *
         * @return Heading reference to the current heading.
         */
        public function &getCurrent(): Heading
        {
            return $this->allHeadings[$this->curIndex];
        }

        /**
         * Access to a heading at a given index.
         *
         * @param int $index the index for the heading to get
         *
         * @return Heading reference to the heading, null if invalid index
         */
        public function &getAt(int $index): ?Heading
        {
            if ($index < 0 || $index >= count($this->allHeadings)) {
                return null;
            }
            return $this->allHeadings[$index];
        }

        /**
         * Go to next heading and get it.
         *
         * @return Heading reference to the new current heading
         *                 null if no more heading available
         */
        public function &getNext(): ?Heading
        {
            if ($this->curIndex >= count($this->allHeadings) - 1) {
                return null;
            }
            $this->curIndex += 1;
            return $this->allHeadings[$this->curIndex];
        }


        /**
         * Check if a heading is the last available between two levels.
         *
         * @param int $index the index of the heading in the array, or -1 for current exploration index
         * @param int $start the highest heading level (1 = top)
         * @param int $end   the lowest heading level (> start)
         *
         * @return bool true if the current heading is the last available between start and $end,
         *              false if there is at least one relevant heading after it.
         */
        public function isHeadingLastBetween(int $index = -1, int $start = 1, int $end = 9): bool
        {
            if ($index < 0) {
                $index = $this->curIndex;
            }
            for ($i = $index + 1; $i < count($this->allHeadings); $i += 1) {
                if ($this->allHeadings[$i]->getLevel() >= $start && $this->allHeadings[$i]->getLevel() <= $end) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Check if a heading is between two levels.
         *
         * @param int $index the index of the heading in the array, or -1 for current exploration index
         * @param int $start the highest heading level (1 = top)
         * @param int $end   the lowest heading level (> start)
         *
         * @return bool true if the current heading is the last available between start and $end,
         *              false if there is at least one relevant heading after it.
         */
        public function isHeadingBetween(int $index = -1, int $start = 1, int $end = 9): bool
        {
            if ($index < 0) {
                $index = $this->curIndex;
            }
            if ($this->allHeadings[$index]->getLevel() >= $start && $this->allHeadings[$index]->getLevel() <= $end) {
                return true;
            }
            return false;
        }

        /**
         * Find the first heading in the array for a level after a given line number.
         *
         * @param int       $level    the heading level to look for
         * @param int       $lineNum  the line number where to start search
         *
         * @return int -1 if no heading found, else the index of Heading object
         */
        public function findIndex(int $level = 1, int $lineNum = 0): ?int
        {
            foreach ($this->allHeadings as $index => $object) {
                if ($object->getLineNum() >= $lineNum) {
                    if ($object->getLevel() == $level) {
                        return $index;
                    }
                }
            }
            return -1;
        }

        /** Find a heading with given line number.
         * Return null if not found.
         */
        public function &findByLine(int $lineNum): ?Heading
        {
            foreach ($this->allHeadings as &$heading) {
                if ($heading->getLineNum() >= $lineNum) {
                    return $heading;
                }
            }
            return $this->null;
        }

        /**
         * Check if an index is valid.
         *
         * @param int   $index -1 for current index or a heading index
         * @param Filer $filer the caller object with an error() function, can be null to ignore errors.
         *
         * @return int|null same index if valid, current index if -1, null if invalid
         */
        private function checkIndex(int $index = -1, ?Filer $filer = null): ?int
        {
            if ($index < -1 || $index >= count($this->allHeadings)) {
                if ($filer) {
                    $filer->error("invalid heading index $index");
                }
                return $this->null;
            }
            if ($index == -1) {
                return $this->curIndex;
            }
            return $index;
        }

        /**
         * Get the spacing prefix for a heading and current output mode.
         * The spacing is used in TOC lines before each heading.
         * Depending on the output mode, the spacing can be:
         *
         * MDPURE: 3 spaces for each level above 1
         * MD/MDNUM : 2 spaces for each level above 1
         * HTML all variants: 4 '&nbsp;' for each level above 1
         *
         * @param int $index index of the heading, -1 to use current exploration index.
         * @see Logger interface
         *
         * @return string the spacing prefix for current output mode, or null if error.
         */
        public function getSpacing(int $index = -1, ?Filer $filer = null): string
        {
            $index = $this->checkIndex($index, $filer);
            if ($index === null) {
                return null;
            }
            $heading = &$this->allHeadings[$index];
            $repeat = 2;
            switch ($this->outputMode) {
                case OutputModes::MDPURE:
                    $repeat += 1;
                    // intentionnal fall-through
                case OutputModes::MD:
                case OutputModes::MDNUM:
                    return \str_repeat(' ', $repeat * ($heading->getLevel() - 1));
                default:
                    // all html modes
                    return \str_repeat('&nbsp;', 4 * ($heading->getLevel() - 1));
            }
            // impossible case
            if ($filer) {
                $filer->error("impossible case in " . __FUNCTION__);
            }
            return null;
        }

        /**
         * Get the anchor for a heading and current output mode.
         * The anchor is targetted by TOC links in TOC lines.
         * Depending on the output mode, for a unique identifier a{id}, the anchor can be:
         *
         * MDPURE : a Markdown anchor {#a{id}}
         * MD/MDNUM/HTML/HTMLNUM : an id HTML anchor <A id="a{id}">
         * HTMLOLD/HTMLOLDNUM : an name HTML anchor <A name="a{id}">
         *
         * @param int    $index  valid index of the heading (not checked here)
         * @param Filer  $filer  the filer object
         * @see Logger interface
         *
         * @return string the anchor, or null if error.
         */
        public function getAnchor(int $index, ?Filer $filer = null): string
        {
            $id = (string)($this->allHeadings[$index]->getNumber());
            $result = OutputModes::getAnchor($this->outputMode, $id);
            if ($result === null && $filer !== null) {
                $filer->error("invalid output mode {$this->outputMode}");
            }
            return $result;
        }

        /**
         * Get TOC link for current or given heading in a given file.
         * The returned string includes the heading text as legend for the link.
         * The file path must be the output language file relative path where the anchor lies for this link.
         * The caller is responsible for giving the relevant language file name and maximum
         * heading level so the last line can be detected.
         *
         * HTML all variants: <A href="file#id">text</A>
         * MD all variants: [text](<file#id>)
         *
         * @param string $path      the file path where lies the anchor, must be relative to root dir
         * @param int    $index     the index of the heading, -1 to use current exploration index.
         * @param int    $start     the minimum heading level (lowest number of '#'s)
         * @param int    $end       the maximum heading level (biggest number of '#'s)
         * @param Filer  $filer     the filer object
         * @see Logger interface
         *
         * @return string the TOC link, or null if error.
         */
        public function getTOCLink(string $path, int $index, int $start, int $end, Filer $filer): string
        {
            $index = $this->checkIndex($index, $filer);
            if ($index === null) {
                return null;
            }
            $id = $this->allHeadings[$index]->getNumber();
            $text = $this->allHeadings[$index]->getText();
            if ($path == $filer->current()) {
                $path = '';
            }
            switch ($this->outputMode) {
                case OutputModes::MDPURE:
                case OutputModes::MD:
                case OutputModes::MDNUM:
                    return ".all(([.)){$text}.all((](<{$path}#a{$id}>).))";
                default:
                    if ($this->isHeadingLastBetween($index, $start, $end)) {
                        return ".all((<A href=\"{$path}#a{$id}\">{$text}</A>.))";
                    }
                    return ".all((<A href=\"{$path}#a{$id}\">{$text}</A><BR>.))";
            }
            if ($filer) {
                $filer->error("invalid output mode {$this->outputMode}");
            }
            return '';
        }

        /**
         * Get Numbering for current or given heading.
         * The caller provide a Numbering object setup for current file.
         * A dash may prefix text for some output modes if requested (for TOC lines)
         *
         * HTMLNUM/HTMLOLDNUM:  `<numbering>)`
         * MDNUM:               `- <numbering>)`
         * MDPURE with NUM:     `1.`
         * all other variants:  `-`
         *
         * @param int       $index     the index of the heading, -1 to use current exploration index.
         * @param Numbering $numbering the Numbering object in charge of current file numbering scheme.
         * @param bool      $addDash   true to add a dash prefix in MDNUM or non numbered modes
         * @param Filer     $filer     the filer object
         * @see Logger interface
         *
         * @return string the numbering string, or null if error.
        */
        public function getNumberingText(int $index, ?Numbering $numbering, bool $addDash, Filer $filer = null): ?string
        {
            if ($numbering == null) {
                return null;
            }
            if ($index >= 0) {
                // jump to the idnex while updating the numbering
                $index = $this->checkIndex($index, $filer);
                if ($index === null) {
                    return null;
                }
                $numbering->resetSubNumbering();
                for ($i = 0; $i < $index; $i += 1) {
                    $numbering->nextNumber($this->allHeadings[$i]->getLevel());
                }
            } else {
                $index = $this->curIndex;
            }

            $this->curIndex = $index;
            return $numbering->getText($this->allHeadings[$index]->getLevel(), $addDash);
        }

        /**
         * Get text for current or given heading.
         * This must be used sequentially on all headings of the array or numbering won't be consistent
         * regarding previous heading level. The whole sequence must be started with a Numbering and
         * current index reset. Both anchor and numbering parts must be written for all languages.
         *
         * Components for heading line :
         *
         * HTML all variants:  .all((<anchor><numbering> .))<text>\n\n
         * MD all variants:    .all((<numbering> .))<text>.all((<anchor>.))\n\n
         *
         * @param int       $index     index of the heading, -1 to use current exploration index.
         * @param Numbering $numbering the Numbering object in charge of current file numbering scheme.
         * @param Filer     $filer     the filer object
         * @see Logger interface
         *
         * @return string the text for the heading line, or null if error.
         */
        public function getHeadingText(int $index, ?Numbering $numbering = null, ?Filer $filer = null): ?string
        {
            $index = $this->checkIndex($index, $filer);
            if ($index === null) {
                return null;
            }
            $heading = $this->allHeadings[$index];
            $anchor = $this->getAnchor($index, $filer);
            $numberingText = $this->getNumberingText($index, $numbering, false, $filer);
            $text = $heading->getText();
            if (\in_array($this->outputMode, [OutputModes::MD, OutputModes::MDNUM, OutputModes::MDPURE])) {
                return ($numberingText ? '.all((' . $numberingText . '.))' : '') . $text . '.all((' . $anchor . '.))';
            }
            return '.all((' . $anchor . ($numberingText ?? '') . '.))' . $text;
        }

        /**
         * Get TOC full line for current or given heading.
         * This must be used sequentially on all headings of the array or numbering won't be consistent
         * regarding previous heading level. The whole sequence must be started with a Numbering and
         * current index reset.
         *
         * Components for TOC line :
         *
         * HTML all variants:  <spacing><numbering> <TOClink>\n\n
         * MD all variants:    <spacing><numbering> <TOClink>\n\n
         *
         * @param int       $index     index of the heading, -1 to use current exploration index.
         * @param Numbering $numbering the Numbering object in charge of current file numbering scheme or null
         * @param Filer     $filer    the caller object with an error() function, can be null to ignore errors.
         *
         * @return string the full heading line, or null if error or level not within
         *                numbering scheme limits.
         */
        public function getTOCLine(int $index, ?Numbering $numbering, ?Filer $filer = null): ?string
        {
            $index = $this->checkIndex($index, $filer);
            if ($index === null) {
                return null;
            }
            $heading = $this->allHeadings[$index];
            if (!$heading->isLevelWithin($numbering)) {
                return null;
            }
            $spacing = $this->getSpacing($index, $filer);
            $numberingText = $this->getNumberingText($index, $numbering, true, $filer);
            $extension = pathinfo($this->file, PATHINFO_EXTENSION);
            $filename = mb_substr($this->file, 0, - (mb_strlen($extension) + 1));
            $start = $numbering ? (int)$numbering->getStart() : 0;
            $end = $numbering ? (int)$numbering->getEnd() : 10000;
            if ($filename . '.' . $extension == $filer->current()) {
                $text = $this->getTOCLink('', $index, $start, $end, $filer);
            } else {
                // use template variable {extension} which will expand to current extension for this file (e.g. .fr)
                $text = $this->getTOCLink($filename . '{extension}', $index, $start, $end, $filer);
            }
            if (!empty($spacing ?? '') || !empty($numberingText ?? '')) {
                if (empty($numberingText)) {
                    $numberingText = '- ';
                }
                return '.all((' . $spacing . ($numberingText ?? '') . '.))' . $text;
            }
            // no spacing and no numbering: don't need .all(( .)) heading
            return '- ' . $text;
        }
    }
}
