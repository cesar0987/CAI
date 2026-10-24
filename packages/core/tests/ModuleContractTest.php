<?php

declare(strict_types=1);

use Cai\Core\Contracts\ModuleContract;

it('exposes the base module contract', function () {
    $contract = new class implements ModuleContract {
        public function moduleName(): string
        {
            return 'students';
        }

        public function moduleVersion(): string
        {
            return '1.0.0';
        }

        public function description(): string
        {
            return 'Módulo de estudiantes';
        }
    };

    expect($contract->moduleName())->toBe('students');
    expect($contract->moduleVersion())->toBe('1.0.0');
    expect($contract->description())->toBe('Módulo de estudiantes');
});
