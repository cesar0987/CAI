<?php

declare(strict_types=1);

namespace Cai\Core\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

final class SchemaPaginator
{
    /**
     * Apply the module connection before pagination so repeated `on('students')`
     * calls are centralized in one place.
     *
     * @param  EloquentBuilder|QueryBuilder  $query
     */
    public static function paginate(
        EloquentBuilder|QueryBuilder $query,
        string $schema,
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null,
    ): LengthAwarePaginator {
        return $query->on($schema)->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * @param  EloquentBuilder|QueryBuilder  $query
     */
    public static function simplePaginate(
        EloquentBuilder|QueryBuilder $query,
        string $schema,
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null,
    ) {
        return $query->on($schema)->simplePaginate($perPage, $columns, $pageName, $page);
    }
}
