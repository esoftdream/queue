<?php

declare(strict_types=1);

/**
 * This file is part of Esoftdream Queue.
 *
 * (c) Esoftdream
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/src/',
        __DIR__ . '/tests/',
    ])
    ->exclude('build')
    ->append([__FILE__]);

$config = new Config();
$config
    ->setFinder($finder)
    ->setCacheFile('build/.php-cs-fixer.cache')
    ->setRules([
        '@PSR12' => true,
    ]);

return $config;
