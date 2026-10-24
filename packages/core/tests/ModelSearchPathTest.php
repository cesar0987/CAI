<?php

declare(strict_types=1);

use Cai\Core\Models\Model;

it('uses the short table name and keeps schema resolution in the core model contract', function () {
    $model = new class extends Model {
        protected $table = 'students';
    };

    expect($model->getTable())->toBe('students');
    expect($model->getQualifiedTableName())->toBe('"public"."students"');
});

it('allows subclasses to define a custom schema', function () {
    $model = new class extends Model {
        protected $table = 'students';
        protected string $schema = 'students';
    };

    expect($model->getQualifiedTableName())->toBe('"students"."students"');
});
