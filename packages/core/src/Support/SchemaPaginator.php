<?php

declare(strict_types=1);

namespace Cai\Core\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

final class SchemaPaginator
{
    /**
     * Apunta la consulta a la conexión del módulo sin perder sus restricciones.
     *
     * El modelo se clona para no cambiar la conexión del modelo del llamador.
     *
     * @param  Builder<*>  $query
     * @return Builder<*>
     */
    public static function onSchema(Builder $query, string $connection): Builder
    {
        $model = clone $query->getModel();
        $model->setConnection($connection);
        $query->setModel($model);

        return $query;
    }

    /**
     * @param  Builder<*>  $query
     * @param  array<int, string>  $columns
     */
    public static function paginate(
        Builder $query,
        string $connection,
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null,
    ): LengthAwarePaginator {
        return self::onSchema($query, $connection)
            ->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * @param  Builder<*>  $query
     * @param  array<int, string>  $columns
     */
    public static function simplePaginate(
        Builder $query,
        string $connection,
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null,
    ): Paginator {
        return self::onSchema($query, $connection)
            ->simplePaginate($perPage, $columns, $pageName, $page);
    }
}
