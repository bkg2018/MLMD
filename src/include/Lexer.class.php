<?php

/**
 * Multilingual Markdown generator - Lexer class.
 *
 * The Lexer is responsible for transforming an UTF-8 content into a sequence of tokens
 * and send them to output through a Filer. It also centralizes most of the generation
 * parameters like TOC settings, heading numbering scheme, output mode. It preprocesses
 * the list of files to recognize the languages list, the available headings in each file
 * and other things like the first significant text line of each file or the current
 * language stack.
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
 * @package   mlmd_token_class
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'Constants.php';

    // directives and static tokens
    require_once('tokens/TokenNumbering.class.php');
    require_once('tokens/TokenTopNumber.class.php');
    require_once('tokens/TokenLanguages.class.php');
    require_once('tokens/TokenInclude.class.php');
    require_once('tokens/TokenTOC.class.php');
    require_once('tokens/TokenEnd.class.php');
    require_once('tokens/TokenStop.class.php');
    require_once('tokens/TokenOpenAll.class.php');
    require_once('tokens/TokenOpenDefault.class.php');
    require_once('tokens/TokenOpenIgnore.class.php');
    require_once('tokens/TokenClose.class.php');
    require_once('tokens/TokenEmptyLine.class.php');
    require_once('tokens/TokenEOL.class.php');
    require_once('tokens/TokenEscaperSingleBacktick.class.php');
    require_once('tokens/TokenEscaperDoubleBacktick.class.php');
    require_once('tokens/TokenEscaperTripleBacktick.class.php');
    require_once('tokens/TokenEscaperFence.class.php');
    require_once('tokens/TokenEscaperDoubleQuote.class.php');
    require_once('tokens/TokenEscaperMLMD.class.php');
    // on demand directives
    require_once('tokens/TokenText.class.php');             // text for current language
    require_once('tokens/TokenOpenLanguage.class.php');     // .fr((  .en((  etc created when preprocessing .languages directive
    require_once('tokens/TokenHeading.class.php');          // all lines starting with a # become a heading token
    // Headings numbering scheme in toc and in text
    require_once('Numbering.class.php');
    // HTML/MD output modes (set in Lexer and in Numbering)
    require_once('OutputModes.class.php');

    class Lexer
    {
        /** predefined tokens and languages codes directives tokens added by .languages */
        private $mlmdTokens = [];               // keyword => token, e.g. '.{' => TokenEscaperMLMD
        private $mlmdTokensLengths = [];        // keyword => token keyword length
        private $tokenMaxLength = 0;
        private $tokenFENCE = null;             // specific handling for ``` at line beginning
        private $tokenTRIPLEBACKTICK = null;    // specific handling for ``` in text stream

        /** LanguageList object handling all available languages codes */
        private $languageList = null;

        // Preprocessed datas

        /** One HeadingArray for each input file */
        private $allHeadingsArrays = [];
        /** Line numbers after languages directive in each file */
        private $allStartingLines = [];
        /** Numbering scheme for each file, default is CLI parameter or main file directive */
        private $allNumberingScheme = [];
        /** Current numbering for each file */
        private $allNumberings = [];
        /** Starting number for level 1 headings for each file (default to 0 = first number in scheme) */
        private $allTopNumbers = [];

        /** MD/HTML output modes for headings anchors and toc links */
        private $outputMode = OutputModes::MD;

        // Status and settings

        /** true when at least one language has been set */
        private $languageSet = false;
        /** true to wait for .languages directive in each input file */
        private $waitLanguages = true;
        /** stack of tokens names for languages switching, including .all, .default and .ignore */
        private $languageStack = [['code' => DEFLT, 'line' => 0]];
        /** name of current language code */
        private $curLanguage = DEFLT;
        /** number of opened 'ignore', do not output anything when this variable is not 0 */
        private $ignoreLevel = 0;
        /** current character, can be changed by token input processing */
        private $currentChar = '';
        /** Current text flow, to be stored as a text token before next token */
        private $currentText = '';
        /** Current tokens file, will be regularly sent to output when languages stack is empty */
        private $curTokens = [];
        /** flag for a few prints or warnings control */
        private $trace = false;
        /** default numbering scheme, set by '-numbering' CLI parameter */
        private $defaultNumberingScheme = '';
        /** number of previous successives EOL tokens */
        private $eolCount = 0;
        /** number of opened languages (handled by countOpenLanguage and countCloseLanguage) */
        private $languageCount = 0;
        /** false after the first non empty text token */
        private $emptyContent = true;

        /** shortcuts */
        private $tokenCLOSE = null;
        private $tokenALL = null;
        private $tokenDEFLT = null;
        private $tokenEOL = null;

        public function __construct()
        {

            // streamed language directives
            $this->mlmdTokens['.))']        = new TokenClose(null); // for identification
            $this->tokenCLOSE = &$this->mlmdTokens['.))'];
            // NB: TokenOpenLanguage will be instantiated by the .languages directive for each declared language <code>, stored in $this->mlmdTokens['code']
            // NB: TokenText will be instantiated by Lexer for each normal text part, stored in the tokens flow $this->curTokens

            // Other streamed open((
            $this->mlmdTokens['.all((']     = new TokenOpenAll(); 
            $this->tokenALL = &$this->mlmdTokens['.all(('];
            $this->mlmdTokens['.((']        = new TokenOpenDefault(''); 
            $this->tokenDEFLT = &$this->mlmdTokens['.(('];
            $this->mlmdTokens['.default(('] = new TokenOpenDefault(DEFLT);
            $this->mlmdTokens['.ignore((']  = new TokenOpenIgnore(IGNORE);
            $this->mlmdTokens['.!((']       = new TokenOpenIgnore('!');

            // other streamed directives
            $this->mlmdTokens['empty']      = new TokenEmptyLine();                 ///  \n at beginning of line
            $this->mlmdTokens["\n"]         = new TokenEOL();                       ///  \n, must be checked later than TokenEmptyLine
            $this->tokenEOL = &$this->mlmdTokens["\n"];

            // escaped text streamed directives, derived from TokenBaseEscaper
            $this->mlmdTokens['"']          = new TokenEscaperDoubleQuote();        ///  "   - MD double quote escaping
            $this->mlmdTokens['```c']       = new TokenEscaperFence();              ///  ``` cannot be handled using the mlmdTokens array
            $this->tokenFENCE               = $this->mlmdTokens['```c'];
            $this->mlmdTokens['```']        = new TokenEscaperTripleBacktick();
            $this->tokenTRIPLEBACKTICK      = $this->mlmdTokens['```'];
            $this->mlmdTokens['``']         = new TokenEscaperDoubleBacktick();     ///  ``  - MD double backtick escaping, must be checked later than TokenEscaperTripleBacktick
            $this->mlmdTokens['`']          = new TokenEscaperSingleBacktick();     ///  `   - MD single backtick escaping, must be checked later than TokenEscaperDoubleBacktick
            $this->mlmdTokens['.{']         = new TokenEscaperMLMD();               /// .{.} - MLMD escaping

            // single line directives, derived from TokenBaseSingleLine
            $this->mlmdTokens['.numbering'] = new TokenNumbering();
            $this->tokenNUMBERING = &$this->mlmdTokens['.numbering'];
            $this->mlmdTokens['.topnumber'] = new TokenTopNumber();
            $this->tokenTOPNUMBER = &$this->mlmdTokens['.topnumber'];
            $this->mlmdTokens['.languages'] = new TokenLanguages();
            $this->tokenLANGUAGES = &$this->mlmdTokens['.languages'];
            $this->mlmdTokens['.include']   = new TokenInclude();
            $this->tokenINCLUDE = &$this->mlmdTokens['.include'];
            $this->mlmdTokens['.toc']       = new TokenTOC();
            $this->mlmdTokens['.end']       = new TokenEnd();
            $this->mlmdTokens['.stop']      = new TokenStop();
            $this->tokenEND = &$this->mlmdTokens['.end'];
            $this->tokenSTOP = &$this->mlmdTokens['.stop'];

            $this->tokenMaxLength = 0;
            foreach ($this->mlmdTokens as $key => $token) {
                $len = strlen($key);
                if ($len > $this->tokenMaxLength) {
                    $this->tokenMaxLength = $len;
                }
                $this->mlmdTokensLengths[$key] = $len;
            }
        }

        /**
         * Trace setting.
         */
        public function setTrace(bool $yes)
        {
            $this->trace = $yes;
        }

        /**
         * Init status for a ready set of files.
         */
        public function initSet(): void
        {
            $this->languageSet = false;
            $this->waitLanguages = true;
            $this->resetCurrentText();
            $this->ignoreLevel = 0;
        }

        /**
         * Reset current text flow.
         */
        public function resetCurrentText(): void
        {
            $this->currentText = '';
            $this->emptyText = true;
        }

        /**
         * Store current character from an input in current text and go next char.
         *
         * @param Filer|Storage $input the Filer or Storage input object
         */
        public function storeCurrentGoNext(object $input): void
        {
            $this->currentText .= $this->currentChar;
            $this->emptyText = false;
            if (!$input->adjustNextLine()) {
                $this->currentChar = $input->getNextChar();
            }
        }

        /**
         * Add current text as token if not empty, then reset current text.
         *
         * @param Filer $filer the Filer object.
         */
        public function appendTextToken(Filer &$filer): void
        {
            if (!$this->emptyText) {
                $text = new TokenText($this->currentText);
                $this->appendToken($text, $filer);
                unset($text);// free this reference
                $this->resetCurrentText();
            }
        }

        /**
         * Delete last token from stack.
         * Type can be checked for security.
         *
         * @param TokenType $type the optional token type to check against
         */
        public function deleteLastToken(TokenType $type = null): bool
        {
            // checkup
            $count = count($this->curTokens);
            if ($count <= 0) {
                return false;
            }
            $prevToken = &$this->curTokens[$count - 1];
            if ($type !== null) {
                if (!$prevToken->isType($type)) {
                    unset($prevToken);
                    return false;
                }
            }

            // update language stack count
            if ($prevToken->isType(TokenType::CLOSE_DIRECTIVE)) {
                $this->countOpenLanguage();         // assume a close matches a previous open
            } elseif ($prevToken->isType(TokenType::OPEN_DIRECTIVE)) {
                $this->countCloseLanguage();
            }

            // delete token
            array_pop($this->curTokens);
            array_values($this->curTokens);
            $count -= 1;
            if ($prevToken->isType(TokenType::EOL)) {
                $this->eolCount -= 1;
            } elseif ($count >= 2) {
                $this->recalculatePreviousEols();
            }
            unset($prevToken);
            return true;
        }

        /**
         * Calculate the number of previous successive EOLs tokens.
         */
        public function recalculatePreviousEols(): void
        {
            $this->eolCount = 0;
            // Go backward in tokens to look for successive EOLs
            for ($tokenIndex = count($this->curTokens) - 1; $tokenIndex >= 0; $tokenIndex -= 1) {
                $curToken = $this->curTokens[$tokenIndex];
                // count if it's an EOL
                if ($curToken->isType(TokenType::EOL)) {
                    $this->eolCount += 1;
                    continue;
                }
                // else check if it's a close language token
                $languages = [ALL, $this->curLanguage];
                if ($curToken->isType(TokenType::CLOSE_DIRECTIVE)) {
                    // go back to find the matching  open
                    $eolCount = 0;
                    $nonEolFound = false;
                    $countOK = false;
                    for ($index = $tokenIndex - 1; $index >= 0; $index -= 1) {
                        $subCurToken = $this->curTokens[$index];
                        if ($subCurToken->isType(TokenType::OPEN_DIRECTIVE)) {
                            if (in_array($subCurToken->getLanguage(), $languages)) {
                                $countOK = true;
                            }
                            break;//stop counting in the open/close
                        }
                        if ($subCurToken->isType(TokenType::EOL) && !$nonEolFound) {
                            $eolCount += 1;
                            continue;// continue the internal for() inside open/close block
                        }
                        $nonEolFound = true;
                    }
                    // found EOLs?
                    if ($eolCount > 0 && $countOK) {
                        $this->eolCount += $eolCount;
                        // stope counting now if a non EOL was in the open/close block
                        if ($nonEolFound) {
                            break; // finished
                        }
                        // skip the open/close all EOL tokens block and continue with previous tokens
                        $tokenIndex = $index - 1; // skip the OPEN/CLOSE
                        continue;
                    }
                }
                if ($curToken->isType(TokenType::OPEN_DIRECTIVE)) {
                    // skip if it's an open all, they're neutral for EOL counting
                    if (in_array($curToken->getLanguage(), $languages)) {
                        continue;
                    }
                }
                // anything else stops the counting
                break;
            }
        }

        /**
         * Count one more open language token.
         */
        public function countOpenLanguage(): void
        {
            $this->languageCount += 1;
        }
        /**
         * Uncount one open language token.
         */
        public function countCloseLanguage(): void
        {
            $this->languageCount -= 1;
            if ($this->languageCount < 0) {
                $this->languageCount = 0;
            }
        }

        /**
         * Store a token in current tokens array.
         *
         * Cleaning is also done:
         *
         * - do not append more than two successive EOL tokens, ignoring close token
         * - cancel the text token before an EOL if it only hold spacing characters
         * - cancel the EOL before if it follows text or close tokens
         * - close language stack when an EOL is appended after an EOL (empty line)
         *
         * This is used by tokens in their processInput() work to append themselves
         * or other tokens to the lexer current flow of tokens.
         */
        public function appendToken(object &$token, Filer &$filer): void
        {
            $nullToken      = new Token(TokenType::UNKNOWN);
            $count          = count($this->curTokens);
            $prevToken      = $count >= 1 ? $this->curTokens[$count - 1] : $nullToken;
            $prevPrevToken  = $count >= 2 ? $this->curTokens[$count - 2] : $nullToken;
            if ($token->isType(TokenType::EOL)) {
                // Immediately following another EOL in root language mode?
                if (($this->languageCount == 0) && $prevToken->isType(TokenType::EOL)) {
                    // replace the two EOLs by open all / eol / eol / close
                    $this->deleteLastToken();
                    $this->appendToken($this->tokenALL, $filer);
                    $this->appendTokenEOL($filer);
                    $this->appendTokenEOL($filer);
                    $this->appendToken($this->tokenCLOSE, $filer);
                    return;
                }
                //  <EOL><space only text> ?
                if ($prevToken->isType([TokenType::TEXT, TokenType::ESCAPED_TEXT])) {
                    if ($prevPrevToken->isType(TokenType::EOL) && $prevToken->isSpacing()) {
                        $this->deleteLastToken();
                        $this->recalculatePreviousEols();
                        $this->appendTokenEOL($filer);
                        return;
                    }
                }
                // EOL candidate
                $this->recalculatePreviousEols();
                if ($this->eolCount >= 2) {
                    return; // already an empty line
                }
                $this->eolCount += 1; // will add EOL
            } else {
                $this->eolCount = 0; // will add not an EOL
            }

            // check if some text has been written
            if (!$token->isEmpty()) {
                $this->emptyContent = false;
            }
            if ($token->isType(TokenType::CLOSE_DIRECTIVE)) {
                $this->countCloseLanguage();
                $this->recalculatePreviousEols();
            } elseif ($token->isType(TokenType::OPEN_DIRECTIVE)) {
                $this->countOpenLanguage();
                $this->recalculatePreviousEols();
            }
            $this->curTokens[] = $token;
            if ($this->trace) {
                 echo '<TOKEN: ' . \get_class($token) . ">\n";
            }
        }

        /**
         * Store and end-of-line token (EOL).
         */
        public function appendTokenEOL(Filer &$filer): void
        {
            if (!$this->emptyContent) {
                $this->appendToken($this->tokenEOL, $filer);
            }
        }

        /**
         * Check if token stack must be simplified before appending an open language token.
         *
         * If an 'open' token immediately follows a single EOL after a 'close' or a text, then the
         * EOL can be deleted :
         *
         * INPUT stack:  <close> <eol> <<future open>>
         * OUTPUT stack: <close> <<future open>>
         *
         * INPUT stack:  <text> <eol> <<future open>>
         * OUTPUT stack: <text> <<future open>>
         */
        public function adjustCloseOpenSequence(): void
        {
            $count = count($this->curTokens);
            if ($count >= 2) {
                // test if we delete previous spacing text token
                $prevToken = $this->curTokens[$count - 1];
                if ($prevToken->isType([TokenType::EOL])) {
                    $prevToken = $this->curTokens[$count - 2];
                    if ($prevToken->isType([TokenType::CLOSE_DIRECTIVE, TokenType::TEXT, TokenType::ESCAPED_TEXT])) {
                        $this->deleteLastToken();
                    }
                }
            }
        }

        /**
         * Check if token stack must be simplified before appending an EOL token.
         *
         * If an EOL token immediately follows a 'close' after an EOL, then the enclosed
         * EOL can be deleted :
         *
         * INPUT stack:  <noneol> <eol> <close> <<future eol>>
         * OUTPUT stack: <noneol> <close> <<future eol>>
         */
        public function adjustEolCloseEolSequence(Filer &$filer): void
        {
            $count = count($this->curTokens);
            if ($count >= 3) {
                $prevToken = $this->curTokens[$count - 1];
                if ($prevToken->isType([TokenType::CLOSE_DIRECTIVE])) {
                    $prevToken = $this->curTokens[$count - 2];
                    $prevPrevToken = $this->curTokens[$count - 3];
                    if ($prevToken->isType([TokenType::EOL]) && !$prevPrevToken->isType(TokenType::EOL)) {
                        $this->deleteLastToken();// delete close
                        $this->deleteLastToken();// delete eol
                        $this->appendToken($this->tokenCLOSE, $filer); // re-append close
                    }
                }
            }
        }

        /**
         * Set current character.
         * This can be used by tokens in their processInput() work.
         */
        public function setCurrentChar(?string $char): void
        {
            $this->currentChar = $char;
        }

        /**
         * Check if current position in a buffer matches a registered token and return the token.
         * The function doesn't advance position, it just checks if there is a known token at the
         * starting position. Notice that this may fetch characters from input if current buffer
         * doesn't hold enough characters.
         *
         * @param object $input  the input Filer or Storage object
         *
         * @return null|object   the recognized token or null if none, which means
         *                       caller Lexer will have to decide what to do with content
         *                       (e.g. creating text tokens)
         */
        public function fetchToken(object $input): ?Token
        {
            foreach ($this->mlmdTokens as $token) {
                if ($token->identify($input)) {
                    if ($token->isType(TokenType::ESCAPED_TEXT)) {
                        return $token->newInstance();
                    }
                    return $token;
                }
            }

            /*
            $extract = $input->getCurrentChar() . $input->fetchNextCharacters($this->tokenMaxLength);
            // try direct key matching
            foreach ($this->mlmdTokens as $key => &$token) {
                $keylen = strlen($key);
                $match = true;                
                for ($pos = 0 ; $match && ($pos < $keylen) ; $pos += 1) {
                    $match = (mb_substr($extract, $pos, 1) == substr($key, $pos, 1));
                }
                if ($match) {
                    // make sure the token accepts identification
                    if ($token->identify($input)) {
                        // Escaped text tokens have a content so they must be instantiated
                        if ($token->isType(TokenType::ESCAPED_TEXT)) {
                            return $token->newInstance();
                        }
                        // for others, use Lexer's own token instance
                        return $token;
                    }
                }
            }
            // code fence cannot be identified by direct matching, let token check itself
            if ($this->tokenFENCE->identify($input)) {
                return $this->tokenFENCE->newInstance();
            }
            if ($this->tokenTRIPLEBACKTICK->identify($input)) {
                return $this->tokenTRIPLEBACKTICK;
            }
            */
            return null;
        }

        /**
         * Execute the effects of current sequence of tokens.
         *
         * @param object $input  the input Filer or Storage object
         * @param object $filer  the Filer object which will receive outputs and settings
         *
         * @return bool true if all OK and token sequence is emptied, else an error occurred
         */
        public function output(Filer &$filer)
        {
            $result = true;
            $eolCount = 0;
            foreach ($this->curTokens as $token) {
                if (!$token->output($this, $filer)) {
                    $result = false;
                }
            }
            unsetArrayContent($this->curTokens);
            unset($this->curTokens);
            $this->curTokens = [];
            return $result;
        }

        /**
         * Debugging echo of current character and line info.
         * To activate this echo, set the "debug" environment variable to "1" before launching php.
         *
         * @return nothing
         */
        public function debugEcho(string $char): void
        {
            if ($this->trace) {
                echo $char;
            }
        }

        /**
         * Append the token sequence corresponding to a text content to current tokens list.
         * Adjust current, future and previous characters lists.
         * Adjust position in a reference Filer when needed (most probably going to next line).
         * Return a boolean telling if the caller must end current line (append EOL token).
         *
         * Assumes:
         * - language list has been preprocessed ($languageList ready)
         * - $filer is positioned on the content beginning
         *
         * @param string $text        the text to tokenize, preferably a single line but not necessarily
         * @param Filer  $filer       the Filer for any file reference in variable expansion
         * @param bool   $allowOutput flag to allow output in tokenization,
         *                            should be disabled in recursive tokenization (e.g. TokenHeading::processInput)
         *
         * @return bool true if current line ends, false if it continues from curren state in Filer.
         */
        public function tokenize(string $text, Filer &$filer, bool $allowOutput): bool
        {
            $storage = new Storage($text);
            $this->currentChar = $storage->getCurrentChar();

            // now interpret current character
            // important functions are :
            // - storeCurrentGoNext() : store current character into current text and go to next character
            // - gotoNextLine() : skip over next characters until end of current line
            // - fetchNextCharacters() : fetch more characters from input while not changing read position
            // - fetchToken() : try to recognize a token starting at current character
            // 'fetch' means that more characters will be taken from input if needed, but current read position will not change
            do {
                // Identify token starting at this character, or store in current text
                $token = null;
                switch ($this->currentChar) {
                    case null:
                        $token = &$this->tokenEOL;
                        break;
                    case '.':
                        // ignore when followed by space or EOL
                        $nextChar = $storage->fetchNextCharacters(1); // pre-read next character
                        if (($nextChar != ' ') && ($nextChar != "\n") && ($nextChar != "\t")) {
                            $token = $this->fetchToken($storage);
                            // special handling for '.languages' if needed
                            if ((!$this->languageSet) && ($token !== null)) {
                                if ($token->identifyInBuffer('.languages', 0)) {
                                    // language are set by preprocessing, simply acknowledge the directive
                                    $this->languageSet = true;
                                    $filer->setLanguage($this->languageList, DEFLT);
                                    $filer->gotoNextLine();
                                    $storage->gotoNextLine();
                                }
                                // ignore 1) any token before .languages is set 2) .languages directive itself
                                $token = null;
                            } // keep token when after .languages
                        }
                        if ($token == null) {
                            $this->storeCurrentGoNext($storage);
                        }
                        break;
                    case '#':
                        if ($this->languageSet) {
                            // eliminate trivial case (not preceded by EOL)
                            if ($storage->getPrevChar() == "\n") {
                                // find matching heading from preprocessed
                                $headingsArray = $this->allHeadingsArrays[$filer->current()];
                                $heading = $headingsArray->findByLine($filer->getCurrentLineNumber());
                                if ($heading !== null) {
                                    $token = new TokenHeading($heading);
                                    break;
                                }
                            }
                            if ($token == null) {
                                $this->storeCurrentGoNext($storage);
                            }
                        }
                        break;
                    case '`':
                    case '"':
                        if ($this->languageSet) {
                            // start of escaped text?
                            $token = $this->fetchToken($storage);
                            if ($token === null) {
                                if ($this->trace) {
                                    $filer->error("unrecognized escape character [{$this->currentChar}] in text, should translate into a token", __FILE__, __LINE__);
                                }
                                $this->storeCurrentGoNext($storage);
                            }
                        }
                        break;
                    case ' ':
                    case "\n":
                        if ($this->languageSet) {
                            $token = $this->fetchToken($storage);
                            if ($token == null) {
                                $this->storeCurrentGoNext($storage);
                            }
                        }
                        break;
                    default:
                        if ($this->languageSet) {
                            $this->storeCurrentGoNext($storage);
                        }
                        break;
                }
                if ($token) {
                    // save current text in a token, then let new token process input
                    $this->appendTextToken($filer);
                    $token->processInput($this, $storage, $filer);
                    $this->setCurrentChar($storage->getCurrentChar());
                    // if appropriate, output the tokens stack
                    if ($allowOutput && $token->outputNow($this)) {
                        $this->output($filer);
                    }
                    unset($token);
                }
            } while ($this->currentChar != null);
            // process anything left
            $this->appendTextToken($filer);
            unset($storage);
            return true;
        }

        /**
         * Process an opened filer, input and output files ready.
         * Builds sequences of tokens while reading input character by character,
         * and periodically updates outputs when meeting some directives.
         */
        public function process(Filer &$filer): bool
        {
            $relFilename = $filer->current();
            $this->currentChar = '';
            $this->resetCurrentText();
            $this->curTokens = [];

            // skip right after languages directive (only at first time)
            if ($this->waitLanguages && !$this->languageSet) {
                $startLineNumber = $this->allStartingLines[$relFilename];
                do {
                    $lineContent = $filer->getLine(); // read until eol and increment line number
                    if ($this->tokenEND->identifyInBuffer($lineContent, 0)) {
                        break;
                    }
                    if ($this->tokenSTOP->identifyInBuffer($lineContent, 0)) {
                        $stop = $filer->getCurrentLineNumber();//for debug purposes put a bkpkt here
                    }
                    if ($this->currentChar === null) {
                        return false;
                    }
                } while ($filer->getCurrentLineNumber() < $startLineNumber);
                $this->languageSet = true;
            } else {
                $lineContent = $filer->getLine();
            }
            $filer->setLanguage($this->languageList, DEFLT);

            while ($lineContent !== null) {
                if ($this->tokenEND->identifyInBuffer($lineContent, 0)) {
                    $filer->warning(".end directive found");
                    break;
                }
                if ($this->tokenSTOP->identifyInBuffer($lineContent, 0)) {
                    $filer->warning(".stop directive found");//for debug purposes put a bkpkt here
                }
                $curLineNumber = $filer->getCurrentLineNumber();
                if ($this->trace) {
                    echo "[$curLineNumber] $lineContent\n";
                }
                if ($this->tokenize($lineContent, $filer, true)) {
                    $this->appendTokenEOL($filer);
                }
                // DISABLED - to be reworked, a few tokens must be kept ahead current one
                // or EOL cancelling won't work as expected
                // if ((count($this->languageStack) == 0) && ($this->eolCount == 2)) {
                    //$this->output($filer);
                    //$filer->flushOutput();
                //}
                $lineContent = $filer->getLine();
            }
            // process anything left
            $this->appendTextToken($filer);
            // check language stack
            if (count($this->languageStack) > 1) {
                for ($i = 1; $i < count($this->languageStack); $i += 1) {
                    $filer->warning(
                        "a .{$this->languageStack[$i]['code']}(( language opening directive has not been closed",
                        $filer->getInFilename(),
                        $this->languageStack[$i]['line']
                    );
                }
                while (count($this->languageStack) > 1) {
                    $this->popLanguage($filer);
                }
            }
            // terminate outputs
            $this->output($filer);
            $filer->endOutput();  // will add final EOL if needed
            $this->resetCurrentText();
            return true;
        }

        /**
         * Get current language from stack.
         * Returns the array with code and line, or null at top level.
         */
        public function getCurrentLanguage(): ?array
        {
            if (count($this->languageStack) > 1) {
                return $this->languageStack[array_key_last($this->languageStack)];
            }
            return null;
        }

        /**
         * Pushes language and line number on language stack.
         * Name must be an index to $mlmdTokens: 'all', 'ignore', 'default' and each declared language code.
         * Do not push DEFLT if stack is already at root DEFLT
         *
         * @param string $name the new language code to set as current
         *
         * @return bool true if name exists and stack has been updated, false if not
         */
        public function pushLanguage(string $name, object &$filer): bool
        {
            // name must exist as an index
            if (empty($name)) {
                $name = DEFLT;
            }
            //$key = '.' . $name . '((';
            //if (!\array_key_exists($key, $this->mlmdTokens)) {
            if (!$this->languageList->existLanguage($name)) {
                $filer->error("unknown language '$name'");
                return false;
            }
            // don't duplicate root (deflt)
            $stackIt = true;
            if (count($this->languageStack) == 1) {
                if ($name == $this->languageStack[0]['code']) {
                    return false;
                }
            }
            // push new language
            array_push($this->languageStack, ['code' => $name, 'line' => $filer->getCurrentLineNumber()]);
            $this->curLanguage = $name;

            // handle 'ignore'
            if ($name == IGNORE) {
                $this->ignoreLevel += 1;
                // update Filer status
                $filer->setIgnoreLevel($this->ignoreLevel);
            }
            // update Filer status (will be ignored if ignore level > 0)
            $filer->setLanguage($this->languageList, $name);
            return true;
        }

        /**
         * Pop the last language name from stack.
         * Stack is reduced by subtracting its last pushed name. The new current language is set from
         * the new last value on stack.
         * If nothing was in stack, this function returns null and 'all' should be assumed
         * by caller so text will go to all output files when out of languages directives.
         *
         * @return bool false when stack is not popped or empty and language is DEFLT
         */
        public function popLanguage(object $filer): bool
        {
            // pop a level from stack and get new current language
            $popped = ['code' => null];
            $count = count($this->languageStack);
            if ($count > 1) {
                $popped = array_pop($this->languageStack);
                $count -= 1;
                $unstacked = true;
                $this->curLanguage = $this->languageStack[$count - 1]['code'];
            } else {
                // never pop last language on stack
                $this->curLanguage = $this->languageStack[0]['code'];
                $this->ignoreLevel = 0;
                $unstacked = false;
            }
            // handle when popping 'ignore'
            if ($popped['code'] == IGNORE) {
                if ($this->ignoreLevel >= 1) {
                    $this->ignoreLevel -= 1;
                }
                // update Filer status
                $filer->setIgnoreLevel($this->ignoreLevel);
            }
            // update Filer output language
            $filer->setLanguage($this->languageList, $this->curLanguage);
            return $unstacked;
        }

        /**
         * Ready output files for current languages settings and opened input file.
         */
        public function readyOutputs(object $filer): bool
        {
            return $filer->readyOutputs($this->languageList);
        }

        /**
         * Return an array with the list of included files found in a given file.
         */
        public function getIncludedFiles(string $filename, Filer &$filer): ?array
        {
            $includes = [];            
            $path = pathinfo($filename, PATHINFO_DIRNAME);
            $file = fopen($filename, 'rb');
            if ($file === false) {
                $filer->error("could not open [$filename]", __FILE__, __LINE__);
                return null;
            }
            $curLineNumber =  1;
            do {
                $text = getNextLineTrimmed($file, $curLineNumber);
                if (!$text) {
                    break;
                }
                if ($this->tokenEND->identifyInBuffer($text, 0)) {
                    break;
                }
                if ($this->tokenSTOP->identifyInBuffer($text, 0)) {
                    echo "STOP directive found in Lexer::preProcessIncludes loop\n";
                }
                if ($this->tokenFENCE->identifyInBuffer($text, 0)) {
                    $firstLine = $curLineNumber;
                    do {
                        $text = getNextLineTrimmed($file, $curLineNumber);
                    } while ($text !== null && !$this->tokenFENCE->identifyInBuffer($text, 0));
                    if ($text === null) {
                        $filer->error("Code fence (```) unable to find closing code fence", $filename, $firstLine);
                        break;
                    }
                }
                if ($this->tokenINCLUDE->identifyInBuffer($text, 0)) {
                    $lineEnd = trim(mb_substr($text, $this->tokenINCLUDE->getLength()));
                    $filePath = $path . '/' . $lineEnd;
                    if (file_exists($filePath)) {
                        if (!in_array($filePath, $includes)) {
                            $includes[] = $filePath;
                        }
                    } else {
                        $filer->error("included file not found $lineEnd in $path");
                    }
                }
            } while (!feof($file));
            fclose($file);
            // recurse inclusion in included files
            $subIncludes = [];
            foreach ($includes as $file) {
                $array = $this->getIncludedFiles($file, $filer);
                foreach ($array as $subFile) {
                    if (!in_array($subFile, $subIncludes)) {
                        $subIncludes[] = $subFile;
                    }
                }
            }
            // merge with includes array
            foreach ($subIncludes as $subInclude) {
                if (!in_array($includes, $subInclude)) {
                    $includes[] = $subInclude;
                }
            }
            return $includes;
        }

        /**
         * Look for included files in all input files.
         */
        public function preProcessIncludes(Filer &$filer): void
        {
            $includes = [];
            foreach ($filer as $index => $relFilename) {
                $subIncludes = $this->getIncludedFiles($filer->getInputFile($index), $filer);
                foreach ($subIncludes as $subInclude) {
                    if (!in_array($subInclude, $includes)) {
                        $includes[] = $subInclude;
                    }
                }
            }
            foreach ($includes as $subFile) {
                $filer->addInputFile($subFile);
            }
        }

        /**
         * Ready all headings, numberings and languages by reading
         * only related directives from all input files.
         */
        public function preProcess(object $filer): void
        {
            resetArray($this->allHeadingsArrays);
            resetArray($this->allNumberings);
            Heading::init();// reset global headings numbering to 0
            $languageSet = false; // remember if the .languages directive has been read
            $defaultNumberingScheme = $this->defaultNumberingScheme; // start with CLI parameter scheme if any
            // explore each input file ($filer is iterable and returns relative filenames and index)
            foreach ($filer as $index => $relFilename) {
                $filename = $filer->getInputFile($index); // full file path
                if ($filename == null) {
                    continue;
                }
                $file = fopen($filename, 'rb');
                if ($file === false) {
                    $filer->error("could not open [$filename]", __FILE__, __LINE__);
                    continue;
                }
                $headingsArray = new HeadingArray($relFilename);
                $curLineNumber = 0;
                $this->allTopNumbers[$relFilename] = 1;
                // loop on each line
                do {
                    $text = getNextLineTrimmed($file, $curLineNumber);
                    if (!$text) {
                        break;
                    }
                    // handle .end and .stop directive first
                    if ($this->tokenEND->identifyInBuffer($text, 0)) {
                        break;
                    }
                    if ($this->tokenSTOP->identifyInBuffer($text, 0)) {
                        echo "STOP directive found in Lexer::preProcess loop\n";
                    }
                    // handle .languages directive before anything else
                    if ($this->tokenLANGUAGES->identifyInBuffer($text, 0)) {
                        $languageParams = trim(mb_substr($text, $this->tokenLANGUAGES->getLength()));
                        $this->setLanguagesFrom($languageParams, $filer);
                        $languageSet = true;
                        // remember line number for languages directive
                        $this->allStartingLines[$relFilename] = $curLineNumber + 1;
                        continue;
                    }
                    // ignore any line before the .languages directive
                    if ($languageSet === false) {
                        continue;
                    }
                    // handle code fences
                    if ($this->tokenFENCE->identifyInBuffer($text, 0)) {
                        $firstLine = $curLineNumber;
                        do {
                            $text = getNextLineTrimmed($file, $curLineNumber);
                        } while ($text !== null && !$this->tokenFENCE->identifyInBuffer($text, 0));
                        if ($text === null) {
                            $filer->error("Code fence (```) unable to find closing code fence", $filename, $firstLine);
                        }
                        if ($this->trace) {
                            $filer->warning("Code fence found at lines $firstLine-$curLineNumber");
                        }
                    }
                    // handle .topnumber directive
                    if ($this->tokenTOPNUMBER->identifyInBuffer($text, 0)) {
                        $this->allTopNumbers[$relFilename] = (int)(mb_substr($text, $this->tokenTOPNUMBER->getLength()));
                        if (isset($this->allNumberings[$relFilename])) {
                            $this->allNumberings[$relFilename]->setLevelNumber(1, $this->allTopNumbers[$relFilename]);
                        }
                    }
                    // handle .numbering directive
                    if ($this->tokenNUMBERING->identifyInBuffer($text, 0)) {
                        if (!empty($this->allNumberingScheme[$relFilename])) {
                            $filer->warning("numbering scheme overloading for $relFilename", $filename, $firstLine);
                        }
                        $this->allNumberingScheme[$relFilename] = trim(mb_substr($text, $this->tokenNUMBERING->getLength()));
                        $this->allNumberings[$relFilename] = new Numbering($this->allNumberingScheme[$relFilename]);
                        if ($defaultNumberingScheme == null) {
                            $defaultNumberingScheme = $this->allNumberingScheme[$relFilename];
                        }
                    }
                    // store headings
                    if (($text[0] ?? '') == '#') {
                        $heading = new Heading($text, $curLineNumber, $filer);
                        $headingsArray[] = $heading;
                    }
                } while (!feof($file));
                fclose($file);

                // force fake line number for languages directive if none
                if (!isset($this->allStartingLines[$relFilename])) {
                    $this->allStartingLines[$relFilename] = 0;
                }

                // force a level 1 object if no headings
                if (count($headingsArray) == 0) {
                    $heading = new Heading('# ' . $relFilename, 1, $filer);
                    $headingsArray[] = $heading;
                }
                $this->allHeadingsArrays[$relFilename] = $headingsArray;
                unset($headingsArray);
            } // next file

            // check every file gets a numbering if there is a default one
            if ($defaultNumberingScheme != null) {
                foreach ($filer as $relFilename) {
                    if (! \array_key_exists($relFilename, $this->allNumberings)) {
                        $this->allNumberingScheme[$relFilename] = $defaultNumberingScheme;
                        $this->allNumberings[$relFilename] = new Numbering($defaultNumberingScheme, $filer);
                        $this->allNumberings[$relFilename]->setLevelNumber(1, $this->allTopNumbers[$relFilename]);
                    }
                }
            }
            // prepare headings index cross reference
            foreach ($filer as $relFilename) {
                $headingsArray = $this->allHeadingsArrays[$relFilename];
                foreach ($headingsArray as $index => $heading) {
                    $heading->setIndex($index);
                }
            }
        }

        /**
         * Set languages list from a parameter string.
         * This is a relay to LanguagesList::setFrom().
         * Also reprograms output files.
         *
         * @param string $parameters  the parameter string
         * @param object $filer       the Filer object
         *
         * @return bool true if languages have been set correctly and main language was
         *              valid (if 'main=' was in the parameters.)
         */
        public function setLanguagesFrom(string $parameters, object $filer): bool
        {
            if ($this->languageList == null) {
                $this->languageList = new LanguageList();
            }
            $result = $this->languageList->setFrom($parameters);
            if ($result) {
                foreach ($this->languageList as $index => $language) {
                    $key = '.' . $language['code'] . '((';
                    if (!\array_key_exists($key, $this->mlmdTokens)) {
                        //$this->mlmdTokens[$language['code']] = new TokenOpenLanguage($language['code']);
                        $this->mlmdTokens = array($key => new TokenOpenLanguage($language['code'])) + $this->mlmdTokens;
                        $this->mlmdTokensLengths[$key] = mb_strlen($key);
                        if ($this->mlmdTokensLengths[$key] > $this->tokenMaxLength) {
                            $this->tokenMaxLength = $this->mlmdTokensLengths[$key];
                        }
                    }
                }
                $this->languageSet = isset($index);
                if ($filer->hasOpenedFile()) {
                    $filer->readyOutputs($this->languageList);
                }
            }
            return $result;
        }

        /**
         * Set the default numbering scheme before preprocessing.
         *
         * @param string $scheme a string containing numbering scheme.
         *
         * @return nothing
         */
        public function setNumbering(string $scheme): void
        {
            $this->defaultNumberingScheme = $scheme;
        }

        /**
         * Return the text line for a given heading in current file, without the '#' prefixes
         * Handle numbering scheme and current numbering progress.
         *
         * @see HeadingArray class
         */
        public function getHeadingText(Filer &$filer, Heading &$heading): ?string
        {
            $relFilename = $filer->current();
            return $this->allHeadingsArrays[$relFilename]->getHeadingText($heading->getIndex(), $this->allNumberings[$relFilename] ?? null, $filer);
        }

        /**
         * Return all headings arrays.
         */
        public function getAllHeadingsArrays(): array
        {
            return $this->allHeadingsArrays;
        }


        /**
         * return numbering object for a given file.
         * The index is the relative file name vs main file directory.
         */
        public function getNumbering(string $filename): ?object
        {
            if (\array_key_exists($filename, $this->allNumberings)) {
                return $this->allNumberings[$filename];
            }
            return null;
        }
    }
}
