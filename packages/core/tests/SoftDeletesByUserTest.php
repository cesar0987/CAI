<?php

declare(strict_types=1);

use Cai\Core\Traits\SoftDeletesByUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

it('stores the deleting user id before the soft delete is applied', function () {
    Auth::shouldReceive('id')->once()->andReturn(42);

    $model = new class extends Model {
        use SoftDeletesByUser;

        protected $guarded = [];
        public $timestamps = false;
    };

    $model->markDeletedBy();

    expect($model->deleted_by)->toBe(42);
});

it('ignores the mark when there is no authenticated user', function () {
    Auth::shouldReceive('id')->once()->andReturn(null);

    $model = new class extends Model {
        use SoftDeletesByUser;

        protected $guarded = [];
        public $timestamps = false;
    };

    $model->markDeletedBy();

    expect($model->hasAttribute('deleted_by'))->toBeFalse();
});
