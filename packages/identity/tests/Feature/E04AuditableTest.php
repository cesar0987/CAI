<?php

declare(strict_types=1);

use Cai\Identity\Models\User;
use Illuminate\Support\Facades\DB;

it('audits creation and update of models using the Auditable trait', function () {
    $user = User::factory()->create([
        'email' => 'auditable@cai.local',
        'password_changed_at' => now(),
    ]);

    $created = DB::table('audit_events')->where('action', 'user.created')->first();
    expect($created)->not->toBeNull()
        ->and($created->entity_type)->toBe(User::class)
        ->and($created->entity_id)->toBe((string) $user->id);

    $user->update(['name' => 'Nombre cambiado']);

    $updated = DB::table('audit_events')->where('action', 'user.updated')->first();
    expect($updated)->not->toBeNull()
        ->and($updated->severity)->toBe('warning')
        ->and($updated->context)->toContain('Nombre cambiado');
});

it('never writes passwords or MFA secrets into the audit log', function () {
    $plainPassword = 'clave-super-secreta-123';
    $secret = 'JBSWY3DPEHPK3PXP';

    $user = User::factory()->create([
        'email' => 'secretos@cai.local',
        'password' => $plainPassword,
        'mfa_secret' => $secret,
        'password_changed_at' => now(),
    ]);

    $context = DB::table('audit_events')
        ->where('action', 'user.created')
        ->where('entity_id', (string) $user->id)
        ->value('context');

    expect($context)->toContain('[redacted]')
        ->not->toContain($plainPassword)
        ->not->toContain($secret);
});

it('audits deletions as critical and never allows deleting audit rows', function () {
    $user = User::factory()->create(['password_changed_at' => now()]);
    $userId = $user->id;

    $user->delete();

    $deleted = DB::table('audit_events')->where('action', 'user.deleted')->first();
    expect($deleted)->not->toBeNull()
        ->and($deleted->severity)->toBe('critical')
        ->and($deleted->entity_id)->toBe((string) $userId);

    expect(DB::table('audit_events')->count())->toBeGreaterThan(0);
});
