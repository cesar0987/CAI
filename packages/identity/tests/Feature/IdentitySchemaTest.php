<?php

declare(strict_types=1);

use Cai\Identity\Audit\Auditor;
use Cai\Identity\Exceptions\AuditAppendOnlyException;
use Cai\Identity\Models\AuditEvent;
use Cai\Identity\Models\FeatureFlag;
use Cai\Identity\Models\Role;
use Cai\Identity\Models\Setting;
use Cai\Identity\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates the twelve identity tables', function () {
    $tables = [
        'users',
        'password_reset_tokens',
        'sessions',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'settings',
        'feature_flags',
        'audit_events',
        'security_events',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("falta la tabla {$table}");
    }
});

it('enforces a unique email per user', function () {
    User::factory()->create(['email' => 'repetido@cai.local']);

    User::factory()->create(['email' => 'repetido@cai.local']);
})->throws(QueryException::class);

it('stores the mfa secret encrypted at rest', function () {
    $user = User::factory()->create(['mfa_secret' => 'JBSWY3DPEHPK3PXP']);

    $raw = DB::table('users')->where('id', $user->id)->value('mfa_secret');

    expect($raw)->not->toBe('JBSWY3DPEHPK3PXP')
        ->and($user->fresh()->mfa_secret)->toBe('JBSWY3DPEHPK3PXP');
});

it('rejects updates and deletes on audit events (append-only)', function () {
    $event = AuditEvent::create([
        'action' => 'user.created',
        'entity_type' => User::class,
        'entity_id' => '1',
    ]);

    $event->update(['action' => 'user.updated']);
})->throws(AuditAppendOnlyException::class);

it('rejects deleting audit events', function () {
    $event = AuditEvent::create([
        'action' => 'user.created',
        'entity_type' => User::class,
        'entity_id' => '1',
    ]);

    $event->delete();
})->throws(AuditAppendOnlyException::class);

it('records audit events through the auditor on the test connection', function () {
    $actor = User::factory()->create();

    app(Auditor::class)->record('auth.login', User::class, 7, ['ip' => '127.0.0.1'], $actor->id, 99);

    $row = DB::table('audit_events')->where('action', 'auth.login')->first();

    expect($row)->not->toBeNull()
        ->and($row->action)->toBe('auth.login')
        ->and($row->actor_user_id)->toBe($actor->id)
        ->and($row->student_id)->toBe(99);
});

it('uses string primary keys for settings and feature flags', function () {
    Setting::create([
        'key' => 'centro.nombre',
        'value' => 'Centro de Atención Inclusiva',
        'type' => 'string',
        'group_name' => 'centro',
    ]);

    FeatureFlag::create([
        'key' => 'module.billing',
        'description' => 'Apaga el módulo de facturación',
        'is_enabled' => true,
        'scope' => 'module',
    ]);

    expect(Setting::find('centro.nombre')->value)->toBe('Centro de Atención Inclusiva')
        ->and(FeatureFlag::find('module.billing')->is_enabled)->toBeTrue();
});

it('assigns roles to users through spatie with the identity models', function () {
    Role::create(['name' => 'direccion', 'label' => 'Dirección']);
    $user = User::factory()->create();

    $user->assignRole('direccion');

    expect($user->fresh()->hasRole('direccion'))->toBeTrue()
        ->and($user->requiresMfa())->toBeTrue();
});
