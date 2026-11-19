<?php

declare(strict_types=1);

namespace Cai\Identity\Models;

use Cai\Core\Models\Concerns\ResolvesModuleSchema;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * identity.permissions — permiso de spatie/laravel-permission con el schema
 * del módulo y las columnas de dominio (label, module, is_sensitive) de 03.01 §4.
 */
final class Permission extends SpatiePermission
{
    use ResolvesModuleSchema;

    protected string $schema = 'identity';
}
