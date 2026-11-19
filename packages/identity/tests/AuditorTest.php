<?php

declare(strict_types=1);

use Cai\Identity\Audit\Auditor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

afterEach(function (): void {
    // Sin TestCase no hay limpieza automática: el mock de la facade DB
    // no debe filtrarse al resto de la suite Unit.
    Facade::clearResolvedInstances();
    Mockery::close();
});

it('records audit events with the canonical identity table and payload', function () {
    $connection = Mockery::mock();
    $connection->shouldReceive('connection')->andReturnSelf();
    $connection->shouldReceive('getDriverName')->once()->andReturn('pgsql');
    $connection->shouldReceive('table')->once()->with('identity.audit_events')->andReturnSelf();
    $connection->shouldReceive('insert')->once()->with(Mockery::on(function (array $payload) {
        expect($payload['action'])->toBe('user.login');
        expect($payload['entity_type'])->toBe('App\\User');
        expect($payload['entity_id'])->toBe(7);
        expect($payload['severity'])->toBe('info');
        expect($payload['context'])->toBeJson();

        return true;
    }))->andReturn(true);

    // swap explícito: inmune a mocks de DB filtrados de tests anteriores.
    DB::swap($connection);

    app(Auditor::class)->record('user.login', 'App\\User', 7, ['ip' => '127.0.0.1'], 3, 99);
});
