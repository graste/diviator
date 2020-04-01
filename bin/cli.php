#!/usr/bin/env php
<?php declare(strict_types=1);

if (false === \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    echo 'Warning: The console should be invoked via the CLI version of PHP, not the '.\PHP_SAPI.' SAPI'.\PHP_EOL;
}

\set_time_limit(0);

require \dirname(__DIR__).'/vendor/autoload.php';

use Divi\DownloadCommand;
use Symfony\Component\Console\Application;

$application = new Application('Diviator', '0.1.0');
$application->add(new DownloadCommand);
$application->run();