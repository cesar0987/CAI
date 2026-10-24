<?php

declare(strict_types=1);

use Cai\Core\Support\SchemaPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Query\Builder;
use Mockery;

it('centralizes schema-bound pagination on the connection resolver', function () {
    $builder = Mockery::mock(Builder::class);
    $expected = new LengthAwarePaginator([['id' => 1]], 1, 15, 1);

    $builder->shouldReceive('on')->with('students')->once()->andReturnSelf();
    $builder->shouldReceive('paginate')->with(15, ['*'], 'page', null)->once()->andReturn($expected);

    expect(SchemaPaginator::paginate($builder, 'students'))->toBe($expected);
});

it('supports simple pagination on the target schema', function () {
    $builder = Mockery::mock(Builder::class);
    $expected = new Paginator([['id' => 1]], 15, 1);

    $builder->shouldReceive('on')->with('identity')->once()->andReturnSelf();
    $builder->shouldReceive('simplePaginate')->with(20, ['*'], 'page', null)->once()->andReturn($expected);

    expect(SchemaPaginator::simplePaginate($builder, 'identity', 20))->toBe($expected);
});
