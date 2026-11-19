<?php

use NickDeKruijk\Shopwire\Tests\TestCase;

if (!class_exists(\App\Http\Controllers\Controller::class)) {
    require __DIR__ . '/Stubs/Controller.php';
}

uses(TestCase::class)->in('Feature');
