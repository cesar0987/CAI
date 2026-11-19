<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Facade;
use Tests\TestCase;

require_once __DIR__.'/../vendor/autoload.php';

uses(
    TestCase::class,
)->in('Feature', 'Unit', 'Smoke');

// Feature tests de los paquetes: arranque de Laravel + base en memoria.
// Pest solo carga este Pest.php raíz (BootFiles), por eso los bindings
// de los tests de paquetes se declaran acá y no en un Pest.php por paquete.
uses(
    TestCase::class,
    RefreshDatabase::class,
)->in(__DIR__.'/../packages/identity/tests/Feature');

uses(
    TestCase::class,
    RefreshDatabase::class,
)->in(__DIR__.'/../packages/students/tests/Feature');

// Los unit tests de paquetes mockean facades sin TestCase: hay que limpiar
// después de cada test para que los mocks no se filtren entre tests.
afterEach(function (): void {
    Facade::clearResolvedInstances();
    Mockery::close();
});
