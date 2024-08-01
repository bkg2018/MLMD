<?php

/**
 * Multilingual Markdown generator - TokenTOC class
 *
 * This class represents a token for the .toc directive. The processInput for this
 * token will generate a token flow for each table of contents line.
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
 * @package   mlmd_token_toc_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'TokenBaseSingleLine.class.php';

    use MultilingualMarkdown\TokenBaseSingleLine;
    
    /**
     * .TOC directive token.
     *
     * The token stores the parameters following it as a string, and
     * interpret them in output() to send the decorated headings.
     */
    class TokenTOC extends TokenBaseSingleLine
    {
        private $title = 'Table of Contents';    /// title parameter
        private $start = 2;     /// starting level
        private $end = 4;       /// ending level
        private $content = '';
        private $length = 0;
        
        public function __construct()
        {
            parent::__construct(TokenType::SINGLE_LINE_DIRECTIVE, '.toc', true);
        }

        /**
         * Tell if the token is empty of significant text content.
         *
         * @return bool true if the token has *no* text content.
         */
        public function isEmpty(): bool
        {
            return false;
        }

        /**
         * TOC directive input processing.
         */
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            // skip the directive (no need to store)
            $this->skipSelf($input);
            // store the parameters until end of line
            $this->content = trim($input->getLine());
            $this->length = mb_strlen($this->content);
            // scan parameters, syntax is:
            // .toc title=<xxxxxxxxxx> level=<start>-<end>
            $storage = new Storage($this->content);
            $char = $storage->getCurrentChar();
            $paramKeys = ['title=', 'level='];
            $paramKeysLengths = [6, 6];
            while ($char != null) {
                $keyIndex = $storage->isMatchingWords($paramKeys, $paramKeysLengths);
                if ($keyIndex >= 0) {
                    $storage->getString($paramKeysLengths[$keyIndex]);// skip key
                }
                // title?
                switch ($keyIndex) {
                    case 0:
                        $this->title = '';
                        $char = $storage->getCurrentChar();
                        while (($char != null) && ($storage->isMatchingWords($paramKeys, $paramKeysLengths) < 0)) {
                            $this->title .= $char;
                            $char = $storage->getNextChar();
                        }
                        $this->title = trim($this->title);
                        break;
                    case 1:
                        $levels = '';
                        $char = $storage->getCurrentChar();
                        while (($char != null) && ($storage->isMatchingWords($paramKeys, $paramKeysLengths) < 0)) {
                            $levels .= $char;
                            $char = $storage->getNextChar();
                        }
                        $levels = trim($levels);
                        $separatorPos = strpos($levels, '-');
                        if ($separatorPos === false) {
                            // level=N
                            $this->start = (int)$levels;
                            $this->end = $this->start;
                        } elseif ($separatorPos == 0) {
                            // level=-N
                            $this->start = 1;
                            $this->end = (int)substr($levels, 1);
                        } elseif ($separatorPos == strlen($levels) - 1) {
                            // level=N-
                            $this->start = (int)substr($levels, 0, strlen($levels) - 1);
                            $this->end = 9;
                        } else {
                            // level=N-N
                            $this->start = (int)substr($levels, 0, $separatorPos);
                            $this->end = (int)substr($levels, $separatorPos + 1);
                        }
                        break;
                    default:
                        $char = $storage->getNextChar();
                        break;
                }
            }

            // Send tokens for the title
            $lexer->tokenize('.all((', $filer, false);
            $token = new TokenText('## ');
            $lexer->appendToken($token, $filer);
            unset($token);
            $lexer->tokenize('.))', $filer, false);
            $title = $this->title . OutputModes::getAnchor($filer->getOutputMode(), 'toc');
            $lexer->tokenize($title, $filer, false);
            $lexer->appendTokenEOL($filer);
            $allFiles = [];
            $allHeadingsArrays = $lexer->getAllHeadingsArrays();
            if ($this->start == 1) {
                // if level start at 1, must build toc parts for each file
                // sort by level 1 numbering to ensure correct toc order
                $allFiles = [];
                foreach ($allHeadingsArrays as $relFilename => $headingsArray) {
                    $topNumber = $lexer->getTopNumber($relFilename);//0 if none
                    $allFiles[$topNumber] = $relFilename;
                }
                ksort($allFiles);
            } else {
                $allFiles = [$filer->current()];
            }
            // output each file in correct order
            foreach ($allFiles as $relFilename) {
                // output each heading if level between start and end
                $numbering = $lexer->getNumbering($relFilename);
                $headingsArray = $allHeadingsArrays[$relFilename];
                if (($numbering ?? false) && (($numbering->getStart() > $this->end) || ($numbering->getEnd() < $this->start))) {
                    $filer->error("Inconsistent levels in TOC directive or missing numbering scheme", $relFilename, $filer->getCurrentLineNumber());
                    continue;
                }
                foreach ($headingsArray as $index => $heading) {
                    $level = $heading->getLevel();
                    if ($level >= $this->start && $level <= $this->end) {
                        $text = $headingsArray->getTOCLine($index, $numbering, $filer);
                        if ($text === null) {
                            $filer->error("Inconsistent levels in TOC directive or missing numbering scheme", $relFilename, $filer->getCurrentLineNumber());
                            continue;
                        }
                        $lexer->appendTokenEOL($filer);
                        $lexer->tokenize($text, $filer, false);
                        $lexer->output($filer);
                        $filer->flushOutput();
                    }
                }
            }
        }
    }
}
