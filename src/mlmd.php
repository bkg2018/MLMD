<?php

/* Multilingual Markdown generator - Main script
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
 * @package   mlmd_main_script
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */

 // create code coverage if env variable 'coverage' is not 0 and xdebug has been loaded
if (function_exists('xdebug_start_code_coverage') && getenv('coverage')) {
    xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
}

$MLMD_VERSION = "1.0.5";
$MLMD_DATE    = strftime("%F (%X)", filemtime(__FILE__));

require_once 'include/Functions.php';
require_once 'include/Generator.class.php';
use MultilingualMarkdown\Generator as Generator;

//MARK: CLI launch

/// Create the generator instance
if (!function_exists("mb_internal_encoding")) {
    echo "ERROR: the mbstring extension must be enabled in your PHP.INI configuration file.\n";
    exit(1);
}
\mb_internal_encoding('UTF-8');
$generator = new Generator();

/**
 * Array of parameters.
 * Each parameter is defined by its name as index, and value is an array [function to call on value,
 * 'type' of value]
 *  - function starts with a ':'       -> global function
 *  - function does not start with ':' -> Generator member function
 *  - type 'file'   : value must be an existing file
 *  - type 'string' : value is used as is
 *  - type 'number' : value must be a number
 *  - type '-'      : there is no following value for this parameter
 */

$allParams = [
    '-i'            => ['function' => 'addInputFile',         'type' => 'file'],    // set one input file
    '-main'         => ['function' => 'setMainFilename',      'type' => 'file'],    // set a main filename
    '-out'          => ['function' => 'setOutputMode',        'type' => 'string'],  // set Markdown output mode
    '-numbering'    => ['function' => 'setNumbering',         'type' => 'string'],  // set the headings numbering scheme for headings and TOC
    '-od'           => ['function' => 'setOutputDirectory',   'type' => 'string'],  // set the root output directory (else files go into input directory)
    '-trace'        => ['function' => ':setTrace',            'type' => '-'],
    '-h'            => ['function' => ':displayHelp',         'type' => '-'],       // (global function) display help
    '-v'            => ['function' => ':displayVersion',      'type' => '-']        // display MLMD translator version
];
$arg = 1;
while ($arg < $argc) {
    $done = false;
    $key = $argv[$arg];
    if (!array_key_exists($key, $allParams)) {
        echo "WARNING: Unknown parameter $key has been ignored\n";
    } else {
        $def = $allParams[$key];
        $function = $def['function'];
        $type = $def['type'];
        $ok = (mb_strtolower($argv[$arg]) == $key);
        if ($ok) {
            if ($arg > $argc - 1) {
                echo "WARNING: Missing value for parameter $key\n";
                $value = '';
            } elseif ($type != '-') {
                $arg += 1;
                $value = $argv[$arg];
            }
            switch ($type) {
                case 'file':
                    if (!file_exists($value)) {
                        echo "ERROR: input file [$value] doesn't exist\n";
                        $ok = false;
                    }
                    break;
                case 'string':
                    if (empty($value)) {
                        echo "ERROR: empty value for parameter $key\n";
                        $ok = false;
                    }
                    break;
                case 'number':
                    if (empty($value) || !is_numeric($value)) {
                        echo "ERROR: empty or non-numeric value for parameter $key\n";
                        $ok = false;
                    }
                    break;
                case '-':
                    // no value for this parameter
                    $value = $generator;// setTrace() parameter
                    break;
                default:// never happens
                    echo "ERROR: unknown parameter type [$type] in script!\n";
                    exit(1);
            }
            if ($ok) {
                // global or Generator function?
                if ($function[0] == ':') {
                    $function = substr($function, 1);
                    $function($value);
                } else {
                    $generator->$function($value);
                }
            }
            $done = true;
        }
    }
    if (!$done) {
        // unknown parameter: assume an input file
        if (!file_exists($argv[$arg])) {
            echo "ERROR: input file [$argv[$arg]] doesn't exist\n";
        } else {
            $generator->addInputFile($argv[$arg]);
        }
    }
    $arg += 1;
}
$timeStart = microtime(true);
$generator->processAllFiles();
$timeEnd = microtime(true);
$dashes  = str_repeat('-', 79);
$seconds = sprintf("%.02f", $timeEnd - $timeStart);
echo "$dashes\nTOTAL: {$generator->getProcessedLines()} lines processed in $seconds seconds\n";
echo "PHP version: ",phpversion(),"\n";
if (function_exists('xdebug_start_code_coverage') && getenv('coverage')) {
    DumpCoverage();
}