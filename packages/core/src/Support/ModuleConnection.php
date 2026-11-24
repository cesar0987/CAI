<?php

declare(strict_types=1);

namespace Cai\Core\Support;

use Illuminate\Support\Facades\DB;

/**
 * En PostgreSQL cada módulo tiene su conexión con search_path propio. En SQLite
 * (tests y desarrollo local) las conexiones nombradas apuntan a BDs distintas
 * en memoria, así que se usa la conexión por defecto: es la misma que migró
 * RefreshDatabase y la que usan los modelos.
 */
final class ModuleConnection
{
    public static function for(string $schema): ?string
    {
        return DB::getDriverName() === 'pgsql' ? $schema : null;
    }
}
