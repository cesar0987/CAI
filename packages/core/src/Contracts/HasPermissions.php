<?php

declare(strict_types=1);

namespace Cai\Core\Contracts;

/**
 * Contrato de autorización por permisos y roles.
 *
 * Lo implementa el User de identity (vía spatie/laravel-permission).
 * Vive en core para que BasePolicy no dependa de ningún módulo de
 * negocio (frontera Deptrac: Core no importa Identity).
 */
interface HasPermissions
{
    public function hasPermissionTo(string $permission): bool;

    public function hasRole(string $role): bool;
}
