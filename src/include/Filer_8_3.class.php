<?php

/**
 * Multilingual Markdown generator - Filer class
 *
 * The Filer class handles input file reading through a paragraph buffer and output files
 * writing through temporary storage. It is controlled by Lexer and Generator, and output is done by
 * each Token through Lexer control.
 *
 * Input is done through a Storage instance which handles a buffer for one line of text.
 * End of line (EOL) characters are handled separately from the text so Lexer can generate
 * appropriate tokens and control their number. Output retain EOLs until non-EOL text is
 * written and limit their successive number to 2 because Markdown convention forbid
 * multiple empty lines.
 *
 * Text can be written with or without variables expansion. Variable expansion is done in
 * Filer::expand() function, to add variable it can be added there. No special syntax check
 * is done and variables are checked for exact identity, however MLMD convention is to put
 * a self-explanatory name between curved braces, like {main} which expands to the main file name.
 *
 * Output is stored for each language as sequences of parts. A part is a text with a flag telling
 * if this text must be written as is or if it must be expanded with variables first. EOLs are
 * always stored as separate parts and Filer takes care of never writing more than 2 successive EOLs
 * in any file.
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
 * @package   mlmd_main_filer_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'Constants.php';
    require_once 'Utilities.php';
    require_once 'OutputModes.class.php';
    require_once 'Logger.interface.php';
    require_once 'LanguageList.class.php';
    require_once 'Storage.class.php';
    require_once 'OutputPart.class.php';
    require_once 'PicturesMgr.class.php';

    use MultilingualMarkdown\Logger;
    use MultilingualMarkdown\languageList;
    use MultilingualMarkdown\PicturesMgr;

    // MB string functions depending on OS
    $posFunction = 'mb_strpos';
    $cmpFunction = 'strcmp';

    class Filer implements Logger, \Iterator
    {

        // Input filenames, files and reading status

        /** Pictures directory manager  */
        private $picturesMgr;
        /** Array of all the input files - relative to root dir */
        private $allInFilePathes = [];
        /** relative filenames for each filename */
        private $relFilenames = [];
        /** current file name e.g. 'example.mlmd' - relative to root dir */
        private $inFilename = null;
        /** current input file handle */
        private $inFile = null;
        /** input buffers handling object */
        private $storage = null;
        /** number of processed lines after end of process() */
        private $processedLines = 0;

        // Output filenames, files and writing status

        /** as 'example' */
        private $outFilenameTemplate = null;
        /** '<language>' => 'example.md' / 'example.<language>.md' */
        private $outFilenames = [];
        /** '<language>' => file-handle */
        private $outFiles = [];
        /** -main parameter */
        private $mainFilename = null;
        /** root directory, or main file directory */
        private $rootDir = null;
        /** root directory utf-8 length */
        private $rootDirLength = 0;
        /** last written token type */
        private $lastToken = null;
        /** -od parameter (root directory for output files) */
        private $outRootDir = null;
        /** successive EOLs waiting to be written into each output file */
        private $pendingEols = [];
        /** last successive EOLs written into each output file */
        private $previousEols = [];

        // Languages handling (LanguageList class)
        
        /** list of languages, will be set by Lexer via TokenLanguages */
        private $languageList = null;
        /**
         * number of 'ignore' to close in language stack.
         * don't send any output while this variable is not 0
         */
        private $ignoreLevel = 0;
        /** current language code or all, ignore, default  */
        private $curLanguage =  IGNORE;
        /** array of (array of OutputPart), one for each language code */
        private $curOutput = [];
        /** array of OutputPart for default text */
        private $curDefault = [];
        /** language codes will be added by setLanguage */
        private $languageFunction = [];
        /** output mode for anchors and links (mdpure etc) */
        private $outputMode = OutputModes::MD;

        /**
         * Initialize string function names.
         */
        public function __construct(PicturesMgr $pm)
        {
            if (\isWindows()) {
                global $posFunction, $cmpFunction;
                $posFunction = 'mb_stripos' ;
                $cmpFunction = 'strcasecmp';
            }
            $this->storage = new Storage(null);
            $this->picturesMgr = $pm;
        }

        /**
         * Logger function: Send an error or warning to output and php log
         *
         * @param string $type   'error' or 'warning'
         * @param string $msg    the text to display and log.
         * @param string $source optional file name for MLMD script, can be null to ignore
         * @param int    $line   optional line number for MLMD script
         *
         * @return false
         */
        private function log(string $type, string $msg, ?string $source = null, $line = false): bool
        {
            if ($source && $line !== false) {
                error_log("$source:$line MLMD {$type}: $msg");
            } elseif ($this->inFilename) {
                error_log("MLMD {$type}: $msg in {$this->inFilename}:" . $this->storage->getCurrentLineNumber());
            } else {
                error_log("arguments: MLMD {$type}: $msg");
            }
            return false;
        }

        /**
         * Logger interface: Send an error message to output and php log.
         *
         * @param string $msg    the text to display and log
         * @param string $source optional file name for MLMD script, can be null to ignore
         * @param int    $line   optional line number for MLMD script
         *
         * @return false
         */
        public function error(string $msg, ?string $source = null, $line = false): bool
        {
            return $this->log('error', $msg, $source, $line);
        }

        /**
         * Logger interface: Send a warning message to output and php log.
         *
         * @param string $msg the text to display and log
         * @param string $source optional file name for MLMD script, can be null to ignore
         * @param int    $line   optional line number for MLMD script
         *
         * @return false
         */
        public function warning(string $msg, ?string $source = null, $line = false): bool
        {
            return $this->log('warning', $msg, $source, $line);
        }

        /**
         * Iterator interface to relative filenames with foreach()
         */
        private $iteratorIndex = 0;

        public function current(): string
        {
            return $this->relFilenames[$this->iteratorIndex];
        }
        public function key(): int
        {
            return $this->iteratorIndex;
        }
        public function next(): void
        {
            $this->iteratorIndex += 1;
        }
        public function rewind(): void
        {
            $this->iteratorIndex = 0;
        }
        public function valid(): bool
        {
            return isset($this->relFilenames[$this->iteratorIndex]);
        }

        /**
         * Processed lines accessor.
         */
        public function getProcessedLines(): int
        {
            return $this->processedLines;
        }

        /**
         * Current filename accessor.
         */
        public function getInFilename(): ?string
        {
            return $this->inFilename;
        }

        /**
         * Get the current line number for current reading position.
         */
        public function getCurrentLineNumber()
        {
            return $this->storage->getCurrentLineNumber();
        }

        /**
         * Set root directory for relative filenames.
         * Resets all registered input files relative to the new root directory.
         *
         * @param string $rootDir     the root directory, preferably an absolute path.
         * @param bool   $resetInputs true to update the input file pathes and names
         *
         * @return bool false if the directory doesn't exist.
         */
        public function setRootDir(string $rootDir, bool $resetInputs = true): bool
        {
            if (\file_exists($rootDir) && \is_dir($rootDir)) {
                $absoluteRoot = normalizedPath(realpath($rootDir));
                $this->rootDir = rtrim($absoluteRoot, "/\\");
                $this->rootDirLength = mb_strlen($this->rootDir);
                if ($resetInputs) {
                    $this->readyInputs();
                }
                if ($this->picturesMgr) {
                    $this->picturesMgr->setSourceRoot($rootDir);
                }
                return true;
            }
            return $this->error("invalid root directory ($rootDir)", __FILE__, __LINE__);
        }

        /**
         * Get the current root directory.
         *
         * @return string null if no root directory yet, else root directory.
         */
        public function getRootDir(): ?string
        {
            return $this->rootDir;
        }

        /**
         * Set the main file name.
         * The root directory is set to the base directory of this main file.
         * All input files must be relative to the root directory.
         *
         * @param string $name the name of the main template file.
         *                     Default is 'README.mlmd' in the root directory.
         *
         * @return bool false if file doesn't exist
         */
        public function setMainFilename(string $name = 'README.mlmd', bool $resetInputs = true): bool
        {
            global $posFunction;
            // try to find this file name in registered files
            $mainExtension = \getMLMDExtension($name);
            if ($mainExtension === null) {
                $this->error("wrong extension for main MLMD file, should be '.base.md' or '.mlmd'");
                return false;
            }
            $wantedPath = mb_substr($name, 0, - mb_strlen($mainExtension));
            $wantedName = \basename($wantedPath) . '.';

            $mainPath = '';
            foreach ($this->allInFilePathes as $filePath) {
                $posName = $posFunction($filePath, $wantedName, 0);
                if ($posName > 0) {
                    // found, now set root directory to this file base dir
                    $this->setRootDir(mb_substr($filePath, 0, $posName), false);
                    $mainPath = $filePath;
                    break;
                }
            }
            if (empty($mainPath)) {
                // file not found: reset root dir
                $mainPath = normalizedPath(\realpath($name));
                if ($mainPath === false) {
                    return $this->error("main file cannot be found ($name)", __FILE__, __LINE__);
                }
                $this->setRootDir(dirname($mainPath), false);
            }
            // get the base name relative to root dir
            $basename = $this->getBasename($mainPath);
            if ($basename !== false) {
                $this->mainFilename = $basename;
            }
            $this->readyInputs();
            return true;
        }

        /**
         * Set the root output directory for all written files.
         * If this parameter is left to null, the files will be written at the same
         * place as their corresponding input files.
         * If $dir starts with a slash, it is considered an absolute path and will be
         * used as is. If not, it is considered a relative path and will be relative
         * to the starting current directory (cwd).
         */
        public function setOutputDirectory(string $dir): bool
        {
            if (substr($dir, 0, 1) == '/') {
                $test = $dir;
            } else {
                $test = getcwd();
                if (mb_substr($dir, -1, 1) != '/') {
                    $test .= '/';
                }
                $test .= $dir;
            }
            if (!file_exists($test)) {
                mkdir($test, 0755, true);
            }
            $this->outRootDir = $test ;
            $this->picturesMgr->setDestinationRoot($dir);
            return true;
        }

        /**
         * Add a file to the input files array.
         * This must be done before any processing.
         * The file is checked for existence. The full path is stored, if it cannot be found
         * the function doesn't record the file and returns false. If no root directory
         * is set yet, the home directory of the file is set as root.
         *
         * @param string $path the relative or absolute path to the input file. If it is relative,
         *                     then the absolute path is computed by the realpath() function.
         *
         * @return bool true if ok, false if the file doesn't exist or can't be accessed or
         *              has a wrong extension (.mlmd and .base.md are accepted, any other is rejected.)
         */
        public function addInputFile(string $path): bool
        {
            global $posFunction;
            $path = normalizedPath($path);
            // check file extension
            $extension = getMLMDExtension($path);
            if ($extension === null) {
                return $this->error("invalid file extension ($path)");
            }
            // check if it is relative or absolute
            $absolutePath = normalizedPath(realpath($path));
            if ($absolutePath === false) {
                return $this->error("file $path doesn't exist");
            }
            $filePos = $posFunction($absolutePath, $path, 0);
            if ($filePos !== 0) {
                if ($filePos === false) {
                    // delete anything before '/../' or '/./' in relative path
                    foreach (['/../', '/./'] as $pattern) {
                        do {
                            $curPos = \mb_strrpos($path, $pattern);
                            if ($curPos !== false) {
                                $path = mb_substr($path, $curPos + mb_strlen($pattern));
                            }
                        } while ($curPos !== false);
                    }
                    // delete starting '../' or './'
                    foreach (['../', './'] as $pattern) {
                        do {
                            $curPos = ${$this->posFunction}($path, $pattern);
                            if ($curPos === 0) {
                                $path = mb_substr($path, mb_strlen($pattern));
                            }
                        } while ($curPos !== false);
                    }
                    $filePos = ${$this->posFunction}($absolutePath, $path, 0);
                    if ($filePos === false) {
                        // shouldn't happen
                        return $this->error("impossible to find root directory from $path", __FILE__, __LINE__);
                    }
                }
                // relative path: check against root dir or set it
                $baseDir = mb_substr($absolutePath, 0, $filePos - 1);
                if (empty($this->rootDir ?? '')) {
                    $this->setRootDir($baseDir);
                } else {
                    $rootPos = ${$this->posFunction}($absolutePath, $this->rootDir, 0);
                    if ($rootPos === false) {
                        return $this->error("file path ($absolutePath) is not relative to root dir ({$this->rootDir}", __FILE__, __LINE__);
                    }
                }
            }
            // do not store a path twice
            if (!in_array($absolutePath, $this->allInFilePathes)) {
                $this->allInFilePathes[] = $absolutePath;
            }
            return true;
        }

        /**
         * Return the number of input files.
         */
        public function getInputFilesMaxIndex(): int
        {
            return count($this->allInFilePathes) - 1;
        }

        /**
         * Return an input file name.
         * Returns null if the index is invalid.
         *
         * @param int $index an index value between 0 and getInputFilesMaxIndex().
         *
         * @return string|null the file path or null if $index is invalid
         */
        public function getInputFile(int $index): ?string
        {
            if ($index < 0 || $index >= count($this->allInFilePathes)) {
                return null;
            }
            return $this->allInFilePathes[$index];
        }

        /**
         * Return an input file name, relative to root directory.
         * Returns null if the index is invalid.
         *
         * @param int $index an index value between 0 and getInputFilesMaxIndex().
         *
         * @return string|null the root directory relative file path or null if $index is invalid
         */
        public function getRelativeInputFile(int $index): ?string
        {
            if ($index < 0 || $index >= count($this->relFilenames)) {
                return null;
            }
            return $this->relFilenames[$index];
        }

        /**
         * Get basename (no extension) from a filepath, relative to root directory or
         * to main file directory.
         *
         * @param string $path the path to the file. If the path is relative to
         *
         * @return string|bool the base name, without extension and using a path relative
         *                     to rootDir, null if the path is not under rootDir
         *                     or if there is no rootDir (-i script arguments)
         */
        public function getBasename(string $path): ?string
        {
            global $cmpFunction;
            //  build relative path against root dir
            if ($this->rootDir !== null) {
                $rootLen = mb_strlen($this->rootDir);
                $baseDir = mb_substr(normalizedPath(realpath($path)), 0, $rootLen);
                if ($cmpFunction($baseDir, $this->rootDir) != 0) {
                    $this->error("wrong root dir for file [$path], should be [{$this->rootDir}]", __FILE__, __LINE__);
                    return null;
                }
                $extension = getMLMDExtension($path) ?? '';
                $path = mb_substr(normalizedPath(realpath($path)), $rootLen + 1, null);
                return mb_substr($path, 0, -mb_strlen($extension));
            } else {
                if ($this->mainFilename !== null) {
                    // get root dir from main file path
                    $this->rootDir = normalizedPath(dirname(realpath($this->mainFilename)));
                    return $this->getBasename($path);
                }
            }
            $extension = getMLMDExtension(basename($path)) ?? '';
            return basename($path, $extension);
        }

        /**
         * Tell if a file is currently opened and output files can be prepared.
         */
        public function hasOpenedFile()
        {
            return ($this->inFile != null);
        }

        /**
         * Open one of the input files and prepare the output filename template.
         * If another file is already opened for input, it is closed.
         *
         * @param int $index index of the input file in the files array.
         *                   must be between 0 and getInputFileMaxIndex() included.
         *
         * @return bool true if input file was opened correctly, false for any error.
         */
        public function openFile(int $index): bool
        {
            if ($index < 0 || $index > $this->getInputFilesMaxIndex()) {
                return $this->error("invalid index $index for file", __FILE__, __LINE__);
            }
            // open or exit
            $this->closeInput();
            $filename = $this->allInFilePathes[$index];
            $this->inFile = fopen($filename, "rb");
            if ($this->inFile === false) {
                return $this->error("cannot open file $filename", __FILE__, __LINE__);
            }

            // prepare storage object
            if (!isset($this->storage) || ($this->storage == null)) {
                $this->storage = new Storage($this->inFile);
            }

            // retain base name with full path but no extension as template and reset line number
            $extension = \getMLMDExtension($filename);
            if ($this->outRootDir == null) {
                $this->outFilenameTemplate = mb_substr($filename, 0, -mb_strlen($extension));
            } else {
                $outFilePath = $this->outRootDir . '/' . $this->relFilenames[$index];
                $outRootDir = pathinfo($outFilePath, PATHINFO_DIRNAME);
                if (!file_exists($outRootDir)) {
                    mkdir($outRootDir, 0755, true);
                }
                $this->outFilenameTemplate = mb_substr($outFilePath, 0, -mb_strlen($extension));
            }
            $this->inFilename = $filename;
            $this->curLanguage = IGNORE;
            $this->closeOutput();
            // the output files will be opened by the .languages directive for
            // this opened input file based on $this->outFilenameTemplate and languages codes.
            return true;
        }

        /**
         * Close input file.
         *
         * @return false in all cases
         */
        public function closeInput(): bool
        {
            if ($this->inFile != null) {
                fclose($this->inFile);
                unset($this->inFile);
                $this->inFile = null;
            }
            if (isset($this->inFilename)) {
                unset($this->inFilename);
                $this->inFilename = null;
            }
            if (isset($this->outFilenameTemplate)) {
                unset($this->outFilenameTemplate);
                $this->outFilenameTemplate = null;
            }
            $this->processedLines = 0;
            if (isset($this->storage)) {
                $this->processedLines = $this->storage->getCurrentLineNumber();
                $this->storage->close();
                unset($this->storage);
            }
            return false;
        }

        /**
         * Close output files.
         *
         * @return false in all cases
         */
        public function closeOutput(): bool
        {
            foreach ($this->outFiles as &$outFile) {
                if ($outFile != null) {
                    fclose($outFile);
                }
            }
            unset($this->outFiles);
            $this->outFiles = [];
            return false;
        }

        /**
         * Reset the list of input files to the content of a directory and subdirectories.
         * The directory becomes the root directory.
         *
         * @param string $rootDir the root directory where to look for input files
         *
         * @return bool true if directory correctly explored, false for any problem
         */
        public function exploreDirectory(string $rootDir): bool
        {
            $this->closeInput();
            $this->closeOutput();
            $this->setRootDir($rootDir);
            $this->allInFilePathes = exploreDirectory($this->rootDir);
            return true;
        }

        /**
         * Get all input files ready for processing.
         * If the input file array or root directory are not set, use default values:
         * - root directory is set to the current working directory using PHP getcwd()
         * - input files are set to all the mlmd or .base.md files recursively found in and under root directory
         */
        public function readyInputs(): void
        {
            if ($this->mainFilename == null) {
                if (count($this->allInFilePathes) > 0) {
                    $this->setMainFilename($this->allInFilePathes[0]);
                }
            }
            if (empty($this->rootDir ?? '')) {
                $this->setRootDir(getcwd());
            }
            unset($this->relFilenames);
            $this->relFilenames = [];

            foreach ($this->allInFilePathes as $index => $filename) {
                // get relative filename, ignore if not the right root
                $rootLen = mb_strlen($this->rootDir);
                $baseDir = mb_substr($filename, 0, $rootLen);
                if ($baseDir != $this->rootDir) {
                    $this->error("wrong base dir for file [$filename], should be [$this->rootDir]", __FILE__, __LINE__);
                    continue;
                }
                // relative filename is the index for the work arrays
                $this->relFilenames[$index] = mb_substr($filename, $rootLen + 1);
            }
        }



        /**
         * Read a number of characters including the current one and return the string.
         * Return null if already at end of file. The final current position is set
         * on the first character past the string.
         */
        public function getString(int $charsNumber): ?string
        {
            return $this->storage->getString($charsNumber);
        }

        /**
         * Prepare output filenames from the languages set and output template filename.
         * This call must be done after all input files have been set and readyInputs() has
         * been called.
         *
         * @param LanguageList $languageList the LanguageList object
         */
        public function readyOutputs(object $languageList): bool
        {
            if ($this->outFilenameTemplate == null) {
                return $this->error("output file template not set", __FILE__, __LINE__);
            }
            $return = true;
            $this->languageFunction = [ALL => 'outputAll',IGNORE => 'outputIgnore',DEFLT => 'outputDefault'];
            foreach ($languageList as $index => $array) {
                $code = $array['code'] ?? null;
                $this->outFiles[$code] = null;
                if ($languageList->isMain($code)) {
                    $this->outFilenames[$code] = "{$this->outFilenameTemplate}.md";
                } else {
                    $this->outFilenames[$code] = "{$this->outFilenameTemplate}.{$code}.md";
                }
                $this->outFiles[$code] = fopen($this->outFilenames[$code], "wb");
                if ($this->outFiles[$code] == false) {
                    $return &= $this->error("unable to open file {$this->outFilenames[$code]} for writing", __FILE__, __LINE__);
                }
                $this->curOutput[$code] = []; // each [$code] is an array where each [i] is an OutputPart
                $this->languageFunction[$code] = 'outputCurrent';
                $this->pendingEols[$code] = 0;
            }
            $this->curDefault = []; // each [i] is an OutputPart
            $this->languageList = $languageList;
            $this->lastToken = TokenType::FIRST;
            return $return;
        }

        /**
         * Tells if output has been written something significant.
         */
        public function outputStarted(): bool
        {
            return $this->lastToken != TokenType::FIRST;
        }

        //MARK: Relays to storage

        /**
         * Return the previous UTF-8 character .
         *
         * @return null|string previous character ('\n' for EOL).
         */
        public function getPrevChar(): ?string
        {
            return $this->storage->getPrevChar();
        }

        /**
         * Return the current UTF-8 character from current paragraph.
         * Load next paragraph if no paragraph is loaded yet.
         *
         * @return null|string current character ('\n' for EOL), null when file and buffer are finished.
         */
        public function getCurrentChar(): ?string
        {
            return $this->storage->getCurrentChar();
        }

        /**
         * Read and return the next UTF-8 character from current buffer, return null at end of file.
         *
         * @return null|string new current character ('\n' for EOL), null at end of file
         */
        public function getNextChar(): ?string
        {
            return $this->storage->getNextChar();
        }

        /**
         * Skip every character starting at next one until next line starts. Do not read the first character on new line,
         * so at exit the current character is the current line EOL.
         *
         * @return null|string EOL or null at end of file
         */
        public function gotoNextLine(): ?string
        {
            return $this->storage->gotoNextLine();
        }


        /**
         * Read and return the text until the end of line. Do not include
         * the end of line character in the returned text.
         */
        public function getLine(): ?string
        {
            return $this->storage->getLine();
        }

        /**
         * Adjust line number and read position if current character is EOL.
         * Return true if line adjusted, false if nothing done.
         */
        public function adjustNextLine(): bool
        {
            return $this->storage->adjustNextLine();
        }

        /**
         * Look at previous UTF-8 characters.
         * Cannot read more than further the beginning of file or the beginning
         * of current buffer positions. The buffer at most up to 3072 characters before current
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
            return $this->storage->fetchPreviousChars($charsNumber);
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
            return $this->storage->isMatchingWord($word, $length);
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
                if ($this->storage->isMatchingWord($word, $allLengths[$index])) {
                    return $index;
                }
            } 
            return -1;
        }

        /**
         * Set the 'ignore' level.
         * No output will occur while this level is not 0.
         */
        public function setIgnoreLevel(int $level): void
        {
            $this->ignoreLevel = $level;
        }

        /**
         * Set the current output language, also accepts 'all' or 'default'.
         *
         * @param object $languageList the LanguagesList object
         * @param string $language     the language code to set as current
         */
        public function setLanguage(object $languageList, string $language): bool
        {
            if ($this->ignoreLevel > 0) {
                return false;
            }
            if (($languageList == null) || (get_class($languageList) != 'MultilingualMarkdown\LanguageList')) {
                return false;
            }
            if ($languageList->existLanguage($language)) {
                $this->curLanguage = $language;
                return true;
            }
            return false;
        }

        /**
         * Set output mode.
         * If numbering scheme has been set, the output mode will use a numbered format.
         * If not, it will use a non-numbered format.
         * Setting a numbering scheme after setting the output mode will adjust the mode.
         *
         * @param string $name      the output mode name 'md', 'mdpure', 'html' or 'htmlold'
         * @param object $numbering the numbering scheme object or null
         */
        public function setOutputMode(string $name, ?object $numbering): void
        {
            $this->outputMode = OutputModes::getFromName($name);
            if ($numbering !== null) {
                $numbering->setOutputMode($name, $this);
            }
        }

        /**
         * Output mode accessor.
         */
        public function getOutputMode(): int
        {
            return $this->outputMode;
        }

        /**
         * Append text to given language output.
         */
        public function outputLanguage(string $text, string $language, bool $expand): bool
        {
            if (empty($text)) {
                return false;
            }
            // EOLs are stored in waiting queue and written only when non EOL text comes up
            // so they don't get incorrectly written at the end of file. No more than 2 EOLS
            // can be stored to respect Markdown conventions which allow only one empty line
            // at a time. The first EOL ends the current line of text, the second one
            // creates an empty line, after which any EOL will be rejected until a non EOL text
            // is output.
            if ($text == "\n") {
                if ($this->pendingEols[$language] >= 2) {
                    return true;
                }
                $this->pendingEols[$language] += 1;
                return true;
            }
            // send pending EOLs, reset them and then append new text
            $this->curOutput[$language][] = new OutputPart(\str_repeat("\n", $this->pendingEols[$language]), false);
            $this->pendingEols[$language] = 0;
            $this->curOutput[$language][] = new OutputPart($text, $expand);
            return true;
        }
        /**
         * Append default parts to empty language outputs.
         */
        private function fillEmptyOutputs(): void
        {
            if (count($this->curDefault) > 0) {
                foreach ($this->languageList as $index => $array) {
                    $code = $array['code'] ?? null;
                    // no output for this code yet?
                    if ((count($this->curOutput[$code]) == 0) /*&& ($this->pendingEols[$code] == 0)*/) {
                        // copy the default text
                        foreach ($this->curDefault as $part) {
                            $this->outputLanguage($part->text, $code, $part->expand);
                        }
                    }
                }
                // clear the default text now
                unsetArrayContent($this->curDefault);
            }
        }

        /**
         * Output text to current output language and mode.
         *
         * @param string $text      the text to send
         * @param bool   $expand    true if variables must be expanded (headings and text)
         *                          false if they don't (escaped text)
         * @param int    $tokenType the type of token sending this output
         */
        public function output(?string $text, bool $expand, int $tokenType): bool
        {
            if ($this->ignoreLevel > 0) {
                return false;
            }
            if (\array_key_exists($this->curLanguage, $this->languageFunction)) {
                $functionName = $this->languageFunction[$this->curLanguage];
            } else {
                echo "ERROR: unknown language function for $this->curLanguage\n";
                $functionName = 'outputIgnore';
            }
            if (\in_array($tokenType, [TokenType::TEXT, TokenType::ESCAPED_TEXT])) {
                $this->lastToken = $tokenType;
            }
            return $this->$functionName($text, $expand);
        }



        /**
         * Append text to all current languages output buffers.
         * Set status accordingly.
         */
        public function outputAll(string $text, bool $expand): bool
        {
            // 1) Send default text to empty language buffers
            $this->fillEmptyOutputs();

            // 2) append text part to all languages
            foreach ($this->languageList as $index => $array) {
                $code = $array['code'] ?? null;
                $this->outputLanguage($text, $code, $expand);
            }
            return true;
        }

        /**
         * Append text part to default output.
         * First flush current outputs if there is any content for some language.
         */
        public function outputDefault(string $text, bool $expand): bool
        {
            // 1) flush if any non empty outputs
            $empty = true;
            foreach ($this->languageList as $index => $array) {
                $code = $array['code'] ?? null;
                if (count($this->curOutput[$code]) > 0) {
                    $empty = false;
                    break;
                }
            }
            if (!$empty) {
                $this->flushOutput();
            }
            // 2) add to default buffer
            $this->curDefault[] = new OutputPart($text, $expand);
            array_values($this->curDefault);
            return true;
        }

        /**
         * Ignore text output.
         */
        public function outputIgnore(string $text, bool $expand): bool
        {
            return true;
        }
        
        /**
         * Append text to current language output.
         */
        public function outputCurrent(string $text, bool $expand): bool
        {
            return $this->outputLanguage($text, $this->curLanguage, $expand);
        }

        /**
         * Send all output to files.
         */
        public function flushOutput(): bool
        {
            $result = true;

            // 1) send default text to empty language buffers
            $this->fillEmptyOutputs();

            // 2) send to files
            foreach ($this->languageList as $index => $array) {
                $code = $array['code'] ?? null;
                if (!isset($this->outFiles[$code])) {
                    echo "ERROR: unavailable file for code <$code>\n";
                    $result = false;
                    continue;
                }
                $this->previousEols[$code] = 0;
                foreach ($this->curOutput[$code] as $part) {
                    // expand variables if needed
                    $text = $part->expand ? $this->expand($part->text, $code) : $part->text;
                    // write to file
                    if (empty($text)) {
                        continue; // ignore this loop
                    }
                    fwrite($this->outFiles[$code], $text);
                    // reset EOL count on any non eol starting text
                    if ($text[0] != "\n") {
                        $this->previousEols[$code] = 0;
                    }
                    // count ending EOLs
                    $pos = mb_strlen($text) - 1;
                    while ($pos >= 0 && mb_substr($text, $pos, 1) == "\n") {
                        $this->previousEols[$code] += 1;
                        $pos -= 1;
                    }
                }
                unsetArrayContent($this->curOutput[$code]);
                $this->curOutput[$code] = [];
            }
            return $result;
        }

        /**
         * Send all output to files and make sure they finish on an EOL.
         */
        public function endOutput(): void
        {
            $this->flushOutput();
            foreach ($this->languageList as $index => $array) {
                $code = $array['code'] ?? null;
                if ($this->previousEols[$code] < 1) {
                    fwrite($this->outFiles[$code], str_repeat("\n", 1 - $this->previousEols[$code]));
                }
            }
        }

        /**
         * Expand variables in a text.
         * NB: special case for {picture:<filename>} : the file is copied
         * from source path to same path reative to destination.
         */
        public function expand(string $text, string $language): string
        {
            $relFilename = $this->current();
            $baseExtension = \getMLMDExtension($relFilename);
            $basename = mb_substr($relFilename, 0, - mb_strlen($baseExtension));
            $extension = $this->languageList->isMain($language) ? '.md' : ".{$language}.md";
            $result = str_replace('{file}', $basename . $extension, $text);
            $result = str_replace('{filename}', $basename, $text);
            $result = str_replace('{extension}', $extension, $result);
            if ($this->mainFilename !== null) {
                $result = str_replace('{main}', $this->mainFilename . $extension, $result);
            }
            $languageArray = $this->languageList->getLanguage($language);
            $result = str_replace('{language}', $languageArray['code'], $result);
            if ($languageArray['iso']) {
                $result = str_replace('{iso}', $languageArray['iso'], $result);
            } elseif (strpos($result, '{iso}') !== false) {
                $this->warning("ISO code variable found and no associated iso for $language");
            }
            $startPos = mb_strpos($result, '{picture:');
            if ($startPos !== false) {
                $endPos = mb_strpos($result, '}', $startPos + 10); // start search after '{pictures:'
                if ($endPos === false) {
                    $this->error("Picture variable {picture:} lacks ending '}' after " . mb_substr($result, $startPos + 10));
                    $endPos = mb_strlen($result);
                }
                $image = mb_substr($result, $startPos+9, $endPos - ($startPos+9));
                $path = $this->picturesMgr->findRelativePath($image, $language);
                if ($path) {
                    $result = mb_substr($result, 0, $startPos) . $path . mb_substr($result, $endPos + 1);                
                    // syntax for output modes:
                    // markdown : ![](path)
                    // html: <img src="path">
                    switch ($this->outputMode) {
                        case OutputModes::MD:
                        case OutputModes::MDNUM:
                        case OutputModes::MDPURE:
                            $result = "![]($result)";
                            break;
                        default:
                            $result = "<img src=\"$result\">";
                            break;
                    }
                    $this->picturesMgr->copy($image, $language);
                }
            }
            return $result;
        }
        /**
         * Copy a picture from source to destination.
         */
        public function copyPicture(string $filename, string $language): bool
        {
            return $this->picturesMgr->copy($filename, $language);
    }
    }


}
