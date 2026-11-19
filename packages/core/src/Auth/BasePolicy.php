<?php

declare(strict_types=1);

namespace Cai\Core\Auth;

use Cai\Core\Contracts\HasPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Policy base — 08.04 §2.5 y 04.03 §1.
 *
 * Capa 1 (permiso) primero, siempre; la capa 2 (alcance por registro) la
 * agrega cada policy concreta. Negación temprana, sin efectos secundarios,
 * determinista.
 *
 * Desviaciones documentadas del snippet de 08.04:
 * - El tipo del usuario es Authenticatable + contrato HasPermissions de
 *   core, no el User de identity: Core no puede depender de Identity
 *   (Deptrac). El contrato lo implementa el User real.
 * - `permissionPrefix` es un método abstracto: PHP no admite propiedades
 *   abstractas.
 * - Defensa en profundidad: una cuenta inactiva no puede nada, aunque
 *   conserve permisos asignados (04.01: la baja revoca el acceso).
 */
abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Prefijo del permiso requerido, p. ej. 'identity.users'.
     */
    abstract protected function permissionPrefix(): string;

    protected function can(Authenticatable $user, string $ability): bool
    {
        if (! $user instanceof HasPermissions) {
            return false;
        }

        if (method_exists($user, 'isActive') && ! $user->isActive()) {
            return false;
        }

        return $user->hasPermissionTo($this->permissionPrefix().'.'.$ability);
    }
}
