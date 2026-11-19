<?php

declare(strict_types=1);

use Cai\Identity\Filament\Resources\AuditEventResource;
use Cai\Identity\Mfa\Totp;
use Cai\Identity\Models\User;
use Database\Seeders\IdentitySeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(IdentitySeeder::class);
});

function panelUser(string $roleName): User
{
    $user = User::factory()->create(['password_changed_at' => now()]);
    $user->assignRole($roleName);

    return $user->fresh();
}

it('redirects guests to the panel login', function () {
    $this->get('/panel/users')->assertRedirect(route('filament.panel.auth.login'));
});

it('serves the six resources to roles with permission', function () {
    $user = panelUser('direccion');

    foreach (['users', 'roles', 'audit-events', 'security-events', 'settings', 'feature-flags'] as $resource) {
        $this->actingAs($user)
            ->get('/panel/'.$resource)
            ->assertOk();
    }
});

it('denies direct URLs to roles without permission', function () {
    $this->actingAs(panelUser('secretaria'))->get('/panel/users')->assertForbidden();
    $this->actingAs(panelUser('recepcion'))->get('/panel/audit-events')->assertForbidden();
    $this->actingAs(panelUser('finanzas'))->get('/panel/roles')->assertForbidden();
    $this->actingAs(panelUser('profesional'))->get('/panel/settings')->assertForbidden();

    // admin_tecnico ve auditoría pero NO roles
    $tecnico = panelUser('admin_tecnico');
    $this->actingAs($tecnico)->get('/panel/audit-events')->assertOk();
    $this->actingAs($tecnico)->get('/panel/roles')->assertForbidden();
});

it('exposes audit as read-only: no create or delete routes', function () {
    $this->actingAs(panelUser('direccion'));

    $this->get('/panel/audit-events/create')->assertNotFound();
    expect(AuditEventResource::canCreate())->toBeFalse();
});

it('shows the TOTP challenge and authenticates only with a valid code', function () {
    $secret = Totp::generateSecret();
    $user = User::factory()->create([
        'email' => 'mfa-panel@cai.local',
        'password_changed_at' => now(),
        'mfa_secret' => $secret,
        'mfa_enabled_at' => now(),
    ]);

    $this->withSession(['mfa.pending_user_id' => $user->id, 'mfa.remember' => false])
        ->get(route('mfa.challenge'))->assertOk()->assertSee('Verificación en dos pasos');

    $this->withSession(['mfa.pending_user_id' => $user->id, 'mfa.remember' => false])
        ->post(route('mfa.verify'), ['code' => '000000'])
        ->assertSessionHasErrors('code');
    $this->assertGuest();

    $this->withSession(['mfa.pending_user_id' => $user->id, 'mfa.remember' => false])
        ->post(route('mfa.verify'), ['code' => Totp::code($secret)])
        ->assertRedirect();
    $this->assertAuthenticatedAs($user);

    expect(DB::table('audit_events')->where('action', 'auth.login')->count())->toBe(1)
        ->and(session('mfa.pending_user_id'))->toBeNull();
});

it('never authenticates without a pending MFA challenge', function () {
    $this->get(route('mfa.challenge'))->assertRedirect(route('filament.panel.auth.login'));

    $this->post(route('mfa.verify'), ['code' => '123456'])
        ->assertRedirect(route('filament.panel.auth.login'));
    $this->assertGuest();
});

it('creates the first operator with cai:user and records the action', function () {
    $this->artisan('cai:user', [
        '--email' => 'direccion@cai.local',
        '--name' => 'Dirección del Centro',
        '--role' => 'direccion',
        '--password' => 'clave-segura-12345',
    ])->assertSuccessful();

    $user = User::where('email', 'direccion@cai.local')->first();
    expect($user)->not->toBeNull()
        ->and($user->hasRole('direccion'))->toBeTrue();

    expect(DB::table('audit_events')->where('action', 'user.created')->count())->toBe(1);
    expect(DB::table('security_events')->where('event_type', 'role.changed')->count())->toBe(1);

    $this->artisan('cai:user', [
        '--email' => 'direccion@cai.local',
        '--name' => 'Dirección del Centro',
        '--role' => 'direccion',
        '--password' => 'clave-segura-12345',
    ])->assertFailed();
});
