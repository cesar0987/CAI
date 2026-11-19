<?php

declare(strict_types=1);

namespace Cai\Identity\Console;

use Cai\Identity\Audit\Auditor;
use Cai\Identity\Models\Role;
use Cai\Identity\Models\SecurityEvent;
use Cai\Identity\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Alta de la primera cuenta de Dirección desde consola, sin exponer un
 * registro público. El panel no se puede usar sin un usuario con rol.
 *
 *   php artisan cai:user --email=direccion@cai.local --name="Dirección" \
 *     --role=direccion --password=...
 */
final class CreateUserCommand extends Command
{
    protected $signature = 'cai:user
        {--email= : Email de acceso}
        {--name= : Nombre completo}
        {--role= : Rol a asignar (direccion, admin_tecnico, …)}
        {--password= : Contraseña provisional (mínimo 12 caracteres)}';

    protected $description = 'Crea un usuario de identidad con un rol y registra la acción';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->option('email')));
        $name = trim((string) $this->option('name'));
        $roleName = trim((string) $this->option('role'));
        $password = (string) $this->option('password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'role' => $roleName, 'password' => $password],
            [
                'email' => ['required', 'email', 'unique:users,email'],
                'name' => ['required', 'min:3'],
                'role' => ['required', 'exists:roles,name'],
                'password' => ['required', 'min:12'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'status' => 'active',
            'password_changed_at' => now(),
        ]);

        $user->assignRole(Role::where('name', $roleName)->firstOrFail());

        app(Auditor::class)->record('user.created', User::class, $user->id, [
            'role' => $roleName, 'email' => $email,
        ], $user->id);

        SecurityEvent::create([
            'event_type' => 'role.changed',
            'severity' => 'warning',
            'user_id' => $user->id,
            'reason' => "Alta de usuario con rol {$roleName} desde cai:user",
            'ip_address' => request()->ip(),
        ]);

        $this->info("Usuario {$email} creado con rol {$roleName}.");

        return self::SUCCESS;
    }
}
