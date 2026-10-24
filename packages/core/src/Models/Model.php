<?php

declare(strict_types=1);

namespace Cai\Core\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder;

abstract class Model extends EloquentModel
{
    /**
     * Schemas are configured per connection and default to public.
     * Some modules (identity, students, care, etc.) will override this value.
     */
    protected string $schema = 'public';

    /**
     * Return the short table name used by Eloquent while the connection search_path
     * stays scoped to the current schema.
     */
    public function getTable(): string
    {
        if (! empty($this->table)) {
            return $this->table;
        }

        return parent::getTable();
    }

    /**
     * Produce a schema-qualified table name for PostgreSQL-style resolution,
     * even before the connection resolver is available.
     */
    public function getQualifiedTableName(): string
    {
        $table = $this->getTable();
        $schema = $this->resolveSchema();

        return $this->quoteIdentifier($schema) . '.' . $this->quoteIdentifier($table);
    }

    /**
     * Ensure PostgreSQL sessions use the module schema before the query runs.
     */
    public function newQuery(): Builder
    {
        $query = parent::newQuery();

        if (! static::$resolver) {
            return $query;
        }

        if ($this->usesPostgresConnection()) {
            $this->setSearchPath($this->resolveSchema());
        }

        return $query;
    }

    protected function resolveSchema(): string
    {
        $configuredSchema = $this->schema ?? 'public';

        if ($this->getConnectionName() !== null) {
            $connectionConfig = config('database.connections.' . $this->getConnectionName());

            if (is_array($connectionConfig) && isset($connectionConfig['search_path'])) {
                $configuredSchema = (string) $connectionConfig['search_path'];
            }
        }

        return trim((string) $configuredSchema, '"') ?: 'public';
    }

    protected function usesPostgresConnection(): bool
    {
        if (! static::$resolver) {
            return false;
        }

        $connection = $this->getConnection();

        if (! $connection) {
            return false;
        }

        return $connection->getDriverName() === 'pgsql';
    }

    protected function setSearchPath(string $schema): void
    {
        if (! static::$resolver || $schema === '') {
            return;
        }

        $statement = sprintf('SET search_path TO %s, public', $this->quoteIdentifier($schema));
        $this->getConnection()->statement($statement);
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
