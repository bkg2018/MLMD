<?php

exit(0); // MUST BE REWORKED

declare(strict_types=1);

namespace MultilingualMarkdown {

    require_once 'src/include/Generator.class.php';

    use PHPUnit\Framework\TestCase;
    use MultilingualMarkdown\Generator;

//    require_once 'Generator.class.php';

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
     * @package   mlmd_generator_unit_tests
     * @author    Francis Piérot <fpierot@free.fr>
     * @copyright 2020 Francis Piérot
     * @license   https://opensource.org/licenses/mit-license.php MIT License
     * @link      TODO
     */
    class GeneratorTest extends TestCase
    {
        public function testInitialization()
        {
            $generator = new Generator();
            $generator->addInputFile('testdata/test.mlmd');
            $generator->setMainFilename("test.mlmd");
            $generator->addInputFile('testdata/subdata/secondary.mlmd');
            $generator->addInputFile('testdata/subdata/tertiary.mlmd');
            $generator->setOutputDirectory(realpath('.') . '/out');
            $generator->processAllFiles();

            $this->assertTrue(true);
        }
    }
}
