<?php

declare(strict_types=1);

namespace Cai\Identity\Models;

use Cai\Core\Models\Concerns\ResolvesModuleSchema;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * identity.roles — rol de spatie/laravel-permission con el schema del módulo
 * y las columnas de dominio (label, description) de 03.01 §3.
 */
final class Role extends SpatieRole
{
    use ResolvesModuleSchema;

    protected string $schema = 'identity';
}
