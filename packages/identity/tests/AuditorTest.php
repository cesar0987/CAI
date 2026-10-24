<?php

declare(strict_types=1);

use Cai\Identity\Audit\Auditor;
use Illuminate\Support\Facades\DB;

it('records audit events with the canonical identity table and payload', function () {
    DB::shouldReceive('table')->once()->with('identity.audit_events')->andReturnSelf();
    DB::shouldReceive('insert')->once()->with(Mockery::on(function (array $payload) {
        expect($payload['action'])->toBe('user.login');
        expect($payload['entity_type'])->toBe('App\\User');
        expect($payload['entity_id'])->toBe(7);
        expect($payload['severity'])->toBe('info');
        expect($payload['context'])->toBeJson();

        return true;
    }))->andReturn(true);

    app(Auditor::class)->record('user.login', 'App\\User', 7, ['ip' => '127.0.0.1'], 3, 99);
});
