<?php

use Tests\TestCase;

require_once __DIR__.'/../vendor/autoload.php';

if (function_exists('pest')) {
    // Pest is loaded
}

uses(
    TestCase::class,
)->in('Feature', 'Unit');
