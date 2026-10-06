<?php

declare(strict_types=1);

require __DIR__ . '/TestCase.php';

$testFiles = glob(__DIR__ . '/unit/*Test.php') ?: [];
sort($testFiles);

foreach ($testFiles as $testFile) {
    require $testFile;
}

echo sprintf("%d arquivo(s) de teste executado(s).\n", count($testFiles));
