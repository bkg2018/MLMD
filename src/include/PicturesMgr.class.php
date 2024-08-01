<?php

/**
 * Multilingual Markdown generator - pictures manager
 *
 * The PicturesMgr class handles .picturesroot directive and finding te pictures
 * listed in .picture directives within the source text.
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
 * @package   mlmd_picturesmgr_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {
   
    require_once 'Constants.php';

    class PicturesMgr
    {
        private  $sourceRoot = '';
        private  $picturesRoot = '';
        private  $destinationRoot = '';
        private  $lexer;

        public function __construct(Lexer $lexer)
        {
            $this->lexer = $lexer;
            $lexer->setPicturesMgr($this );
        }
        /**
         * Set the source files root directory
         */
        public function setSourceRoot(string $path) {
            $this->sourceRoot = $path;
            if (!empty($path) && (substr($path, -1, 1) != '/')) $this->sourceRoot .= '/';
        }
        /**
         * Set a pictures directory name. Keep empty if no specific directory.
         */
        public function setRoot(string $path) {
            $this->picturesRoot = $path;
            if (!empty($path) && (substr($path, -1, 1) != '/')) $this->picturesRoot .= '/';
        }
        /**
         * Set the destination directory for copied files.
         */
        public function setDestinationRoot(string $path)
        {
            $this->destinationRoot = $path;
            if (!empty($path) && (substr($path, -1, 1) != '/')) $this->destinationRoot .= '/';
        }

        /**
         * Copy a picture source file to its destination.
         */
        public function copy(string $filename, ?string $language) : bool {
            $relPath = $this->findRelativePath($filename, $language);
            if ($relPath == null) return false;
            $source = $this->sourceRoot . $this->picturesRoot . $relPath;
            $destination = $this->destinationRoot . $this->picturesRoot . $relPath;
            if (substr($destination, 0, 1) !== '/') {
                $destination = getcwd() . '/' . $destination;
            }
            copy($source, $destination);
        }

        /**
         * Returns a picture path relative to pictures root directory.
         * If the picture is found in the current language code subdirectory, the
         * return value is the relative path followed by the filename. If it is not found
         * there but is found in the root, the filename is returned as is. If the
         * picture file is not found, null is returned.
         */
        public function findRelativePath(string $filename, ?string $language) : ?string {
            $path = $this->findRelativeDir($filename, $language);
            if ($path !== null) {
                return empty($path) ? $filename : $path . '/' . $filename;
            }
            return null;
        }
        /**
         * Returns a picture directory relative to pictures root directory.
         * If the picture is found in the current language code subdirectory, the
         * return value is the relative path where the filename can bbe found.
         * If it is not found there but is found in the root, an empty string
         * is returned. If the picture file is not found, null is returned.
         *
         * NB: there is no ending '/' in the returned path
         */
        public function findRelativeDir(string $filename, ?string $language) : ?string {
            if (file_exists($this->sourceRoot . $this->picturesRoot . $language . '/' . $filename)) return $language;
            if (file_exists($this->sourceRoot . $this->picturesRoot . $filename)) return '';
            return null;
        }
    }
}
