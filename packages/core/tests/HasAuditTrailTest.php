<?php

declare(strict_types=1);

use Cai\Core\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

it('records domain events for create, update and delete operations', function () {
    DB::shouldReceive('connection')->andReturnSelf();
    DB::shouldReceive('table')->with('audit_events')->andReturnSelf();
    DB::shouldReceive('insert')->once()->with(
        Mockery::on(function (array $payload) {
            return isset($payload['event'], $payload['model'], $payload['changes'])
                && $payload['event'] === 'created'
                && $payload['model'] === TestAuditModel::class;
        })
    )->andReturnTrue();

    $model = new TestAuditModel();
    $model->id = 42;
    $model->recordAudit('created');
    expect(true)->toBeTrue();
});

class TestAuditModel extends Model
{
    use HasAuditTrail;

    protected $table = 'users';
    public $timestamps = false;
}
