<?php

declare(strict_types=1);

require_once 'src/include/Generator.class.php';
use MultilingualMarkdown\Generator;
mb_internal_encoding('UTF-8');

//xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);

$generator = new Generator();
$generator->setTrace(true);
$generator->setOutputMode('html');
$generator->setNumbering('1::&I:-,2::1:-,3::1');
$generator->addInputFile('testdata/test.mlmd');
$generator->setMainFilename("test.mlmd");
$generator->addInputFile('testdata/subdata/secondary.mlmd');
$generator->addInputFile('testdata/subdata/tertiary.mlmd');
$generator->setOutputDirectory(realpath('.') . '/out');
$generator->processAllFiles();

exit(0);

DumpCoverage();
