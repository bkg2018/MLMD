<?php

/**
 * Multilingual Markdown generator - Generator class
 *
 * This is the main entry class for MLMD conversion. Parameters for the process
 * handling classes objects like Filer, Numbering, Lexer, Storage are forwarded by
 * Generator from the command line arguments to the handling classes with
 * minimal interpretation or checking. It has ownership of the Lexer and Filer
 * instances for the whole process.
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
 * @package   mlmd_main_generator_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {
    
    require_once 'Constants.php';
    require_once 'Logger.interface.php';
    require_once 'Heading.class.php';
    require_once 'HeadingArray.class.php';
    require_once 'Utilities.php';
    if (\getenv('debug')) {
        require_once 'debugFiler.class.php';
        require_once 'debugLexer.class.php';
    } else {
        require_once 'Filer.class.php';
        require_once 'Lexer.class.php';
    }
    use MultilingualMarkdown\Lexer;
    use MultilingualMarkdown\debugLexer;

    /**
     * Generator class.
     * Accept input parameters and files, process all input files and generate output files.
     */
    class Generator implements Logger
    {
        //------------------------------------------------------------------------------------------------------
        //MARK: Members
        //------------------------------------------------------------------------------------------------------

        // Handling classes instances
        private $filer = null;                  /// Filer instance, input and output files handling
        private $lexer = null;                  /// Lexer instance, transform text into token list

        // Settings
        private $outputModeName = '';           /// from -out command line argument
        private $waitLanguages = true;          /// wait for .languages directive in each file
        private $processedLines = 0;            /// computed by processAllFiles() for status display only
        
        // Initialize handlers and default settings
        public function __construct()
        {
            $this->filer = /*(getenv("debug") != 0) ? new DebugFiler() : */ new Filer();
            $this->lexer = /*(getenv("debug") != 0) ? new DebugLexer() : */new Lexer();
            $this->outputModeName = 'md';
        }

        /**
         * Trace control accessor.
         */
        public function setTrace(bool $yes)
        {
            $this->lexer->setTrace($yes);
        }

        /**
         * processed lines accessors.
         */
        public function getProcessedLines(): int
        {
            return $this->processedLines;
        }
        
        //------------------------------------------------------------------------------------------------------
        //MARK: Logger interface (relayed to Filer object)
        //------------------------------------------------------------------------------------------------------

        /**
         * Logger interface: Send an error message to output and php log.
         *
         * @param string $msg    the text to display and log.
         * @param string $source optional file name for MLMD script, can be null to ignore
         * @param int    $line   optional line number for MLMD script
         *
         * @return false
         */
        public function error(string $msg, ?string $source = null, $line = false): bool
        {
            return $this->filer->error($msg, $source, $line);
        }

        /**
         * Logger interface: Send a warning message to output and php log.
         *
         * @param string $msg the text to display and log.
         * @param string $source optional file name for MLMD script, can be null to ignore
         * @param int    $line   optional line number for MLMD script
         *
         * @return false
         */
        public function warning(string $msg, ?string $source = null, $line = false): bool
        {
            return $this->filer->warning($msg, $source, $line);
        }

        //------------------------------------------------------------------------------------------------------
        //MARK: Settings
        //------------------------------------------------------------------------------------------------------

        /**
         * Add an input file by path, can bne relative or absolute.
         *
         * The file must have either '.mlmd' or '.base.md' extension or it is rejected.
         *
         * The path can be relative to the current directory (returned by getcwd())
         * which is generally the one where MLMD has been called from, or it can be
         * relative to a previously set root directory in Filer class.
         *
         * If there is no root directory in Filer yet, the base directory of the
         * added input file will be used as root directory for further added files which
         * will have to be in or under this root directory.
         *
         * @param string $path a relative or absolute file path ending with .mlmd
         *                     or .base.md extension.
         *
         * @return bool true if the file has been added correctly, false in case of error.
         */
        public function addInputFile(string $path): bool
        {
            return $this->filer->addInputFile($path);
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
        public function setMainFilename(string $name = 'README.mlmd'): bool
        {
            return $this->filer->setMainFilename($name);
        }

        /**
         * Set the root output directory, default is same as main input file directory.
         */
        public function setOutputDirectory(string $dir): bool
        {
            return $this->filer->setOutputDirectory($dir);
        }

        /**
         * Set the output mode.
         * Default is MD style links.
         *
         * @param string $mode 'htmlold' to set HTML mode (<A name> links and anchors),
         *                     'html' to set HTML mode (<A id> links and anchors),
         *                     'md for MD mode ([]() links and {:# } anchors)
         */
        public function setOutputMode(string $mode): void
        {
            if (!OutputModes::isValid($mode)) {
                $this->error("invalid output mode $mode, using \'md\'");
                $mode = 'md';
            }
            $this->outputModeName = $mode;
        }

        /**
         * Set the numbering scheme.
         *
         * @param string $scheme a string containing numbering scheme.
         */
        public function setNumbering(string $scheme): void
        {
            $this->lexer->setNumbering($scheme);
        }

        /**
         * Set the 'wait .languages directive' flag.
         * If this flag is set, each input file processing will ignore anything preceding the .languages
         * directive. The languages are set by preprocessing all the input files before processing,
         * so this flag will not make mlmd actually wait for .languages directives but it makes them
         * mandatory.
         * Flag set TRUE:  mlmd will ignore lines of text which precede .languages
         * Flag set FALSE: mlmd will process lines of text which precede .languages
         * As a corollary, if the flag is set to FALSE then only one .languages directive is necessary
         * over the set of all input files, and if it is set to TRUE then input files not featuring
         * a .languages directive will generate empty output because all lines will be ignored.
         */
        public function setWaitLanguages(bool $yes): void
        {
            $this->waitLanguages = $yes;
        }
        
        //------------------------------------------------------------------------------------------------------
        //MARK: TOOLS
        //------------------------------------------------------------------------------------------------------

        /**
         * Find the included files, languages directives and all headings and sub headings
         * in the set of input files before processing them.
         *
         * - The languages directives found in all files will preset the list of output files
         *   for each language for each input file.
         *
         * - The headings will be stored for TOC generation and possibly numbering.
         *
         * Files with no headings will receive a level 1 heading using their filename
         * so that TOC can point to them. If no languages directive has been found at all
         * this is an error and the function will return false.
         *
         * This function reads the files without going through Storage class and only looks
         * for languages directives and headings, skipping over code fences and some
         * escape text sequences.
         */
        public function preProcess(): void
        {
            $this->filer->readyInputs();
            $this->lexer->preProcessIncludes($this->filer);
            $this->filer->readyInputs();
            $this->filer->setOutputMode($this->outputModeName, null);
            $this->lexer->preProcess($this->filer);
        }

        //------------------------------------------------------------------------------------------------------
        //MARK: Public main entry
        //------------------------------------------------------------------------------------------------------
        
        /**
         * Process the input files list.
         * Files must be added to the list using addInputFile() function.
         * If no file has been added, process the files found in current directory
         * and sub directories.
         *
         * @return bool true if processing done correctly, false if any error.
         */
        public function processAllFiles(): bool
        {
            if ($this->filer->getInputFilesMaxIndex() < 0) {
                $this->filer->exploreDirectory(getcwd());
            }
            $dashes = str_repeat('=', 60);
            $this->preProcess();
            $this->lexer->initSet();
            $this->processedLines = 0;
            foreach ($this->filer as $index => $relFilename) {
                echo "$dashes\nProcessing file: $relFilename ";
                $timeStart = microtime(true);
                if (!$this->process($index)) {
                    echo "\n";
                    return false;
                }
                $time = sprintf("%.1f", microtime(true) - $timeStart);
                echo "{$this->filer->getProcessedLines()} lines in {$time} seconds\n";
                $this->processedLines += $this->filer->getProcessedLines();
            }
            return true;
        }

        /**
         * Process one of the input files and generate its output files.
         * This process reads the input file stream, detects and interprets directives,
         * expand variables and sends output to files.
         *
         * Note: readyInputs() must have been called before any process() takes place.
         *
         * @param int $index index of the input file in the filer object.
         *
         * @return bool true if input file processed correctly, false if any error.
         */
        public function process(int $index): bool
        {
            if (!$this->filer->openFile($index)) {
                return false;
            }
            $this->lexer->readyOutputs($this->filer);
            $this->lexer->process($this->filer);
            $this->filer->closeOutput();
            $this->filer->closeInput();
            return true;
        }
    }
}
