<?php

/**
 * Multilingual Markdown generator - base Token class
 *
 * The Lexer::tokenize() function transforms an UTF-8 text buffer into an array of successive
 * parts of different types like text, escaped text, language directives, end of lines etc.
 * The Token class is the base class for each of these possible parts.
 *
 * Some token types are only recognized when another one is interpreted. For example,
 * escaped text is only found when the opening escaper token is found. Most tokens can
 * not happen inside escaped text except the ones closing the escaped sequence. The opening
 * token interprets the text flow and detects both the escaped text and the closing escape
 * sequence while Lexer only finds the opning token.
 *
 * Construction parameters depend on the Token: not all tokens need a keyword or a content.
 * Some tokens are unique in the whole system and never change, while some have a content
 * and need to be instanciated for each content.
 *
 * A Token is responsible for a few tasks:
 *
 * - self-identification against a given input or buffer content and position
 * - do any processing with input, advancing current position where appropriate
 * - tell Lexer if it should process outputs after this token
 * - send output to a Filer when asked for
 * - tell Lexer to append it to the token flow when needed
 *
 * Input processing should be done only after a positive self identification: the token will not
 * check for this. Some tokens do no process a buffer but rather simply store an information:
 * then processInput() will do nothing and will advance the position only right after the token.
 *
 * During processInput(), the token can use Lexer::appendToken() to append itself to the tokens
 * or add other tokens depending on its needs. TokenHeading for example doesn't append itself
 * but rather appends a sequence of tokens to compose the whole heading content including
 * the '#' prefix, the optional numbering and the needed anchors for table of content links.
 * See TokenHeading class for more details.
 *
 * The Token::output() function is called by Lexer to output some content to output files.
 * The outputs are done through the Filer class instance which is given to output().
 * Tokens which have nothing to output will simply do nothing in the function, other will
 * rather act on Lexer to update the generation context. Both Filer and Lexer instances
 * are given as parameter to output() so it can work on them.
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

    require_once 'TokenTypes.class.php';

    /**
     * Token base class.
     * Represents a directive or text part.
     */
    class Token
    {
        protected $type;       /// int value from TokenType enum consts.

        /**
         * Initialize the token with a TokenType.
         *
         * @param int $type a value from TokenType
         * @see TokenType
         */
        public function __construct(int $type)
        {
            if ($type < TokenType::FIRST || $type > TokenType::LAST) {
                $this->type = TokenType::UNKNOWN;
            } else {
                $this->type = $type;
            }
        }

        /**
         * Let the token self-identify against a Filer or Storage object.
         *
         * @param object $input the Filer or Storage object
         *
         * @return bool true if theh current token can be found at current position and buffer content.
         */
        public function identify(object $input): bool
        {
            return false;
        }

         /**
         * Let the token self-identify against an UTF-8 buffer and position.
         *
         * @param string $buffer a buffer holding UTF-8 content
         * @param int    $pos    the position in $buffer where to start identification
         *
         * @return bool true if the token recognizes itself at the given position in the
         *              given buffer.
         */
        public function identifyInBuffer(?string $buffer, int $pos): bool
        {
            return false;
        }

        /**
         * Skip over the token itself in the input object.
         * This doesn't store anything and is mainly for use by the directives
         * tokens themselves.
         *
         * @param object $input the input object which must be a Filer or Storage instance.
         */
        protected function skipSelf(object $input): void
        {
        }

        /**
         * Check if the token is of a given type.
         *
         * @param array|int $type the token type to test against, or an array of types
         *
         * @return true if the token is of the given type
         */
        public function isType($type): bool
        {
            if (is_array($type)) {
                return \in_array($this->type, $type);
            }
            return ($this->type == $type);
        }

        /**
         * Tell if the token is empty of significant text content.
         *
         * @return bool true if the token has *no* text content.
         */
        public function isEmpty(): bool
        {
            return true;
        }

        /**
         * Return the length of the token identifier.
         */
        public function getLength(): int
        {
            return 0;
        }

        /**
         * Process the input starting at the current position, assuming the token
         * identifier starts at this position.
         *
         * If the current Token has to handle part of the following buffer content,
         * it must process it and update the buffer position to right after any character
         * it takes care of. The corresponding buffer part will not be available
         * to further tokens so the current token must store the content if needed, or
         * withdraw it if it only has informational purposes.
         *
         * The function must update an array of tokens by appending the token itself
         * and also more tokens if it has to create them for its content and work.
         * Some tokens do not append themselves but rather append a sequence of tokens,
         * like TokenHeading which cuts the heading title into separate tokens.
         *
         * Calling the process function with a wrong position will lead to wrong
         * results: it must be called only after a positive self-identification.
         *
         * Default behaviour is to append itself and do nothing more.
         *
         * @param Lexer  $lexer  the Lexer object to use to append tokens
         * @param object $input  the Filer or Storage input handling object, positionned on the token start
         * @param Filer  $filer  the Filer input object, for any needed file informations (see TokenHeading)
         */
        public function processInput(Lexer $lexer, object $input, Filer &$filer = null): void
        {
            $lexer->appendToken($this, $filer);
        }
 
        /**
         * Return a text where ascii control codes are replaced by [n].
         * This is only for debugging.
         */
        protected function debugTextPart(string $text): string
        {
            $result = '';
            for ($pos =  0; $pos < mb_strlen($text); $pos += 1) {
                $c = mb_substr($text, $pos, 1);
                $result .= $c < ' ' ? '[' . ord($c) . ']' : $c;
            }
            return $result;
        }

        /**
         * Return a summary of the text token content with neutralized control codes
         * and max length of 60 characters.
         */
        public function debugText(int $maxLength = 60): string
        {
            $result = '';
            if (isset($this->length) && isset($this->content)) {
                if ($this->length < $maxLength) {
                    return $this->debugTextPart($this->content);
                }
                $start = mb_substr($this->content, 0, $maxLength / 2);
                $end = mb_substr($this->content, -$maxLength / 2);
                $result = $this->debugTextPart($start) . '...' . $this->debugTextPart($end);
            }
            return $result;
        }

       /**
         * Output the token content to the Filer object or change its settings.
         * The token must handle whatever it has to do with the output files and lexer output context:
         * send text content, change current language, send raw text, etc.
         *
         * The default implementation here only displays a warning message that this token
         * class do no output. All token classes shgould implement output().
         *
         * @param Lexer $lexer the Lexer object which has current output context (language etc)
         * @param Filer $filer the Filer object which receives outputs and settings
         *
         * @return bool false if output met some error, true if all worked fine.
         */
        public function output(Lexer &$lexer, Filer &$filer): bool
        {
            /*
            $class = get_class($this);
            $backslash = strrpos($class, '\\');
            $lexer->debugEcho('WARNING: no output() for class ' . substr($class, $backslash + 1) . "\n");
            */
            return true;
        }

        /**
         * Tell if a token must process output immediately after being stored.
         * If the answer of this function is true, the calling Lexer will send call output()
         * on all the current tokens stack and then empty it. See Lexer::tokenize() for
         * details.
         *
         * This doesn't mean output will immediately go to output files, as they are only
         * written to when all output bufferinug is flushed. See Filer class for details.
         *
         * @return bool true to make caller call all current tokens output() function and
         *              empty the current tokens stack.
         */
        public function outputNow(Lexer $lexer): bool
        {
            return false;
        }
    }
}
