<?php

exit(0); // MUST BE REWORKED

declare(strict_types=1);

namespace MultilingualMarkdown;

use PHPUnit\Framework\TestCase;
use MultilingualMarkdown\Numbering;
use MultilingualMarkdown\OutputModes;

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
 * @package   mlmd_numbering_unit_tests
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */
class NumberingTest extends TestCase
{
    public function error($msg): void
    {
        error_log("Error in Numbering: $msg");
    }


    // Test roman numbers
    public function testRomans()
    {
        $test = Numbering::getRoman(2020);
        $this->assertEquals('MMXX', $test);
        $test = Numbering::getRoman(125);
        $this->assertEquals('CXXV', $test);
        $test = Numbering::getRoman(3999);
        $this->assertEquals('MMMCMXCIX', $test);
        $test = Numbering::getFromRoman('MMMCMXCIX');
        $this->assertEquals(3999, $test);
    }

    // Test with a null Numbering, should always return empty numbering
    public function testNoNumbering()
    {
        $numbering = new Numbering('', $this);
        $numbering->resetNumbering();
        $test = $numbering->getText(1, true);
        $this->assertEmpty($test);
        $test = $numbering->getText(2, true);
        $this->assertEmpty($test);
    }

    /**
     * Test various levels and numbering schemes.
     */
    public function testHeadingsLevels()
    {
        $numbering = new Numbering('1:Chapter:A:-,2::1:.,3::1:.');
        $numbering->setLevelLimits(1, 3);
        $test = $numbering->getText(1, true);
        $this->assertEquals('Chapter A) ', $test);
        $test = $numbering->getText(1, true);
        $this->assertEquals('Chapter B) ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('B-1) ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('B-2) ', $test);
        $test = $numbering->getText(3, true);
        $this->assertEquals('B-2.1) ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('B-3) ', $test);

        // limit to levels 2-3
        $numbering->resetNumbering();
        $numbering->setLevelLimits(2, 3);
        $test = $numbering->getText(1, true);
        $this->assertEquals('', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('1) ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('2) ', $test);
        $test = $numbering->getText(3, true);
        $this->assertEquals('2.1) ', $test);
        
        // try roman numbers and another separator
        $numbering = new Numbering('1/.((Chapter.)).fr(Chapitre.))/&I/-,2//&i/.,3//1/.');
        $numbering->setLevelLimits(1, 3);
        $test = $numbering->getText(1, true);
        $this->assertEquals('.((Chapter.)).fr(Chapitre.)) I) ', $test);
        $test = $numbering->getText(1, true);
        $test = $numbering->getText(2, true);
        $this->assertEquals('II-i) ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('II-ii) ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('II-iii) ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('II-iv) ', $test);
        $test = $numbering->getText(3, true);
        $this->assertEquals('II-iv.1) ', $test);
        $test = $numbering->getText(3, true);
        $this->assertEquals('II-iv.2) ', $test);
        $test = $numbering->getText(3, true);
        $this->assertEquals('II-iv.3) ', $test);
        $test = $numbering->getText(3, true);
        $this->assertEquals('II-iv.4) ', $test);
    }

    /** pure Markdown numbering */
    public function testPureMD()
    {
        $numbering = new Numbering('1/.((Chapter.)).fr(Chapitre.))/&I/-,2//&i/.,3//1/.');
        $numbering->setLevelLimits(1, 3);
        $numbering->setOutputMode('mdpure', $this);
        $test = $numbering->getText(1, true);
        $this->assertEquals('1. ', $test);
        $test = $numbering->getText(1, true);
        $this->assertEquals('2. ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('1. ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('2. ', $test);
        $test = $numbering->getText(1, true);
        $this->assertEquals('3. ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('1. ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('2. ', $test);
        $test = $numbering->getText(3, true);
        $this->assertEquals('1. ', $test);
        $test = $numbering->getText(2, true);
        $this->assertEquals('3. ', $test);
    }
}
