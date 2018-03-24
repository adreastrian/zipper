#!/usr/bin/env php
<?php

/**
 * Load autoloader
 */
require __DIR__.'/../vendor/autoload.php';

use Zipper\Zipper;
use Silly\Application;

$version = '1.0.0';

$app = new Application('Laravel Valet', $version);

/**
 * Init plugin releaser
 */
$app->command('zip entry [dest]', function ($entry, $dest) {
    (new Zipper($entry, $dest))->make();
})->descriptions('Init plugin release');

/**
 * Run the application.
 */
$app->run();
