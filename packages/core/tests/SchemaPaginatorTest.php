<?php

declare(strict_types=1);

use Cai\Core\Models\Model;
use Cai\Core\Support\SchemaPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

it('apunta la consulta a la conexión del módulo sin tocar el modelo del llamador', function () {
    $model = new class extends Model
    {
        protected $table = 'students';
    };

    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $queryBuilder->shouldReceive('from')->with('students')->andReturnSelf();
    $query = (new Builder($queryBuilder))->setModel($model);

    $scoped = SchemaPaginator::onSchema($query, 'students');

    expect($scoped->getModel()->getConnectionName())->toBe('students')
        ->and($model->getConnectionName())->toBeNull();
});

it('pagina sobre la conexión indicada conservando las restricciones', function () {
    $model = new class extends Model
    {
        protected $table = 'students';
    };

    $expected = new LengthAwarePaginator([['id' => 1]], 1, 15, 1);
    $builder = Mockery::mock(Builder::class);
    $builder->shouldReceive('getModel')->andReturn($model);
    $builder->shouldReceive('setModel')
        ->withArgs(fn (Model $scoped): bool => $scoped->getConnectionName() === 'students')
        ->once();
    $builder->shouldReceive('paginate')
        ->with(15, ['*'], 'page', null)
        ->once()
        ->andReturn($expected);

    expect(SchemaPaginator::paginate($builder, 'students'))->toBe($expected);
});

it('pagina de forma simple sobre la conexión indicada', function () {
    $model = new class extends Model
    {
        protected $table = 'identity';
    };

    $expected = new Paginator([['id' => 1]], 15, 1);
    $builder = Mockery::mock(Builder::class);
    $builder->shouldReceive('getModel')->andReturn($model);
    $builder->shouldReceive('setModel')
        ->withArgs(fn (Model $scoped): bool => $scoped->getConnectionName() === 'identity')
        ->once();
    $builder->shouldReceive('simplePaginate')
        ->with(20, ['*'], 'page', null)
        ->once()
        ->andReturn($expected);

    expect(SchemaPaginator::simplePaginate($builder, 'identity', 20))->toBe($expected);
});
