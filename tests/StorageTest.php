<?php

//exit(0); // MUST BE REWORKED

declare(strict_types=1);

namespace MultilingualMarkdown;

use \PHPUnit\Framework\TestCase;
use MultilingualMarkdown\Filer;
use MultilingualMarkdown\Storage;

require_once __DIR__ . '/../src/include/Storage.class.php';

/** Copyright 2020 Francis Piérot
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
 * @package   mlmd_storage_unit_tests
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

 /**
  * Must be tested:
  *
  * Function            Change      Update              Return                  Update
  *                     buffer      buffer              buffer                  line
  *                     position    content             content                 number
  *
  * setInputFile        -           yes (1 character)   -                       -
  * close               -           yes (empty)         -
  * setInputBuffer      yes (->0)   yes (replace)       -
  * loadLine            -           yes (\n+content)    -
  * getCurrentChar      -           -                   yes (1 cur char)
  * getPrevChar         -           -                   yes (1 prev char)
  * getNextChar         yes (+1)    yes                 yes (1 new cur char)    yes
  * getLine             yes (+n)    yes                 yes (line with no EOL)  yes
  * gotoNextLine        yes (+n)    yes                 yes (null or \n)        yes
  * getString           yes         yes                 yes (N characters)      yes
  * fetchPreviousChars  -           -                   yes (previous chars)
  * fetchNextCharacters      -           yes                 yes (N characters)
  * isMatching          -           yes                 -
  * 
  * 
  * getCurrentLineNumber
  */
class StorageTest extends TestCase
{
    public function testGetChar()
    {
        $file = fopen(__DIR__ . '/../testdata/test.mlmd', 'rt');
        $this->assertNotFalse($file);
        $storage = new Storage($file);
        $c = $storage->getCurrentChar();
        //echo str_repeat('=', 120), "\n";
        $charNumber = 0;
        while ($c !== null) {
            $charNumber += 1;
            $c = $storage->getNextChar();
        }
        // the test file is 959 characters (some UTF-8 characters are more than 1-byte)
        $this->assertEquals(959, $charNumber);
        fclose($file);
    }

    public function testGetLine()
    {
        $file = fopen(__DIR__ . '/../testdata/test.mlmd', 'rt');
        $this->assertNotFalse($file);
        $storage = new Storage($file);
        do {
            $line = $storage->getLine();
            echo $storage->getCurrentLineNumber(), ':', $line, "\n";
        } while ($line != null);
        \fclose($file);
    }
}
