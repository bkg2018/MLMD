<?php

/**
 * Multilingual Markdown generator - Utilities functions
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
 * @package   mlmd_utilities
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

declare(strict_types=1);

//MARK: Global Utility functions

/**
 * Check if running on Windows.
 * Check if the environment variable SYSTEMROOT exists and include 'Windows' in value.
 *
 * @return true if Windows is detected.
 */
function isWindows(): bool
{
    $systemRoot = getenv('SYSTEMROOT');
    if ($systemRoot) {
        return (stripos($systemRoot, 'windows') !== false);
    }
    return false;
}

/**
 * Unset all content from an array but keep the array itself.
 */
function unsetArrayContent(array &$array)
{
    $key = array_key_last($array);
    while ($key !== null) {
        unset($array[$key]);
        $key = array_key_last($array);
    }
    reset($array);
}
/**
 * Unset all content from an array and reset the array itself to an empty array.
 */
function resetArray(array &$array)
{
    unsetArrayContent($array);
    unset($array);
    $array = [];
}

/**
 * Normalize a path to use unix style separators.
 *
 * @param string|bool|null $path the input path to normalize
 *
 * @return string|bool|null the normalized path
 */
function normalizedPath($path)
{
    if ($path !== false && $path !== null) {
        return str_replace('\\', '/', $path);
    }
    return $path;
}

/**
 * Check if a filename has an MLMD valid extension and get this extension.
 *
 * @param string $filename the file name or path to test.
 *
 * @return string the file extension (.base.md or .mlmd), null if invalid
 *                mlmd file name.
 */
function isMLMDfile(string $filename): ?string
{
    $extension = ".base.md";
    $pos = mb_stripos($filename, $extension, 0);
    if ($pos === false) {
        $extension = ".mlmd";
        $pos = mb_stripos($filename, $extension, 0);
        if ($pos === false) {
            return null;
        }
    }
    return $extension;
}

/**
 * Recursively explore a directory and its subdirectories and return an array
 * of each '.base.md' and '.mlmd' file found.
 *
 * @param string $dirName the directory to test, either relative to current
 *                        directory or absolute path.
 *
 * @return string[] pathes of each file found, relative to $dirName.
 */
function exploreDirectory(string $dirName): array
{
    $dir = opendir($dirName);
    $filenames = [];
    if ($dir !== false) {
        while (($file = readdir($dir)) !== false) {
            if (($file == '.') || ($file == '..')) {
                continue;
            }
            $thisFile = $dirName . '/' . $file;
            if (is_dir($thisFile)) {
                $filenames = array_merge($filenames, exploreDirectory($thisFile));
            } elseif (isMLMDfile($thisFile) !== null) {
                $filenames[] = $thisFile;
            }
        }
        closedir($dir);
    }
    return $filenames;
}

/**
 * Return the next text line, trimmed from spaces.
 * If the file is finished, returns false.
 * Update file position and line number.
 */
function getNextLineTrimmed($file, int &$lineNumber): ?string
{
    $newLine = fgets($file);
    if ($newLine === false) {
        return null;
    }
    $newLine = rtrim($newLine, " \t\n\r") . "\n";
    $lineNumber += 1;
    return $newLine;
}

/**
 * Compare two mbstring utf-8 strings.
 */
function mb_strcmp(string $s1, string $s2): int
{
    $length1 = mb_strlen($s1);
    $length2 = mb_strlen($s2);
    $length = min($length1, $length2);
    for ($i = 0; $i < $length; $i++) {
        $c1 = mb_ord(mb_substr($s1, $i, 1));
        $c2 = mb_ord(mb_substr($s2, $i, 1));
        if ($c1 < $c2) {
            return -1;
        } elseif ($c1 > $c2) {
            return  1;
        }
    }
    if ($length1 < $length2) {
        return -1;
    } elseif ($length1 > $length2) {
        return  1;
    }
    return 0;
}

/**
 * Create a coverage log file for each source.
 */
function DumpCoverage()
{
    $allCoverage = xdebug_get_code_coverage();
    echo "Writing coverage files.\n";
    foreach ($allCoverage as $inFilepath => $coverage) {
        $outFilepath = $inFilepath . '.log';
        $inFile = fopen($inFilepath, "r");
        $outFile = fopen($outFilepath, "w");
        $inLine = fgets($inFile);
        $lineNumber = 1;
        while ($inLine != null) {
            $prefix = '   ';
            if (array_key_exists($lineNumber, $coverage)) {
                switch ($coverage[$lineNumber]) {
                    case 1: $prefix = '[*]'; break;
                    case -1:$prefix = '[ ]'; break;
                    case -2:$prefix = ' - '; break;
                    default: break;
                }
            }
            fputs($outFile, $prefix . ' ' . $inLine);
            $inLine = fgets($inFile);
            $lineNumber += 1;
        }
        fclose($inFile);
        fclose($outFile);
    }
}