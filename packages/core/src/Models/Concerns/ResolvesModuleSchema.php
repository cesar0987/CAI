<?php

declare(strict_types=1);

namespace Cai\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Resolución de schema por módulo para conexiones PostgreSQL.
 *
 * En PostgreSQL la conexión fija `search_path` al schema del módulo antes de
 * cada query; en SQLite (desarrollo y tests) los nombres de tabla van sin
 * cualificar y el trait no hace nada. Extraído de `Core\Models\Model` para que
 * también lo puedan usar modelos que no extienden esa base (p. ej. el User
 * autenticable o los modelos de spatie/laravel-permission).
 *
 * Contrato: la clase que usa el trait debe declarar `protected string $schema`
 * (el trait no la declara para evitar el conflicto de composición de PHP).
 */
trait ResolvesModuleSchema
{
    /**
     * Produce el nombre cualificado schema.tabla para PostgreSQL, incluso
     * antes de que el resolver de conexión esté disponible.
     */
    public function getQualifiedTableName(): string
    {
        $table = $this->getTable();
        $schema = $this->resolveSchema();

        return $this->quoteIdentifier($schema).'.'.$this->quoteIdentifier($table);
    }

    /**
     * Asegura que la sesión PostgreSQL use el schema del módulo antes del query.
     *
     * Repite el armado de `newQuery()` de Eloquent para que el tipo genérico
     * quede atado al modelo concreto y no al `Model` base.
     *
     * @return Builder<static>
     */
    public function newQuery(): Builder
    {
        if ($this->usesPostgresConnection()) {
            $this->setSearchPath($this->resolveSchema());
        }

        return $this->registerGlobalScopes($this->newModelQuery());
    }

    protected function resolveSchema(): string
    {
        $configuredSchema = $this->schema;

        if ($this->getConnectionName() !== null) {
            $connectionConfig = config('database.connections.'.$this->getConnectionName());

            if (is_array($connectionConfig) && isset($connectionConfig['search_path'])) {
                $configuredSchema = (string) $connectionConfig['search_path'];
            }
        }

        return trim((string) $configuredSchema, '"') ?: 'public';
    }

    protected function usesPostgresConnection(): bool
    {
        return $this->getConnection()->getDriverName() === 'pgsql';
    }

    protected function setSearchPath(string $schema): void
    {
        if ($schema === '') {
            return;
        }

        $statement = sprintf('SET search_path TO %s, public', $this->quoteIdentifier($schema));
        $this->getConnection()->statement($statement);
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
}
