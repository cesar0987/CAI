<?php

declare(strict_types=1);

use Cai\Students\Student;

it('creates a student with a valid identity and active status', function () {
    $student = new Student(101, 'María López', '12345678A');

    expect($student->id)->toBe(101);
    expect($student->fullName)->toBe('María López');
    expect($student->isActive())->toBeTrue();
});

it('rejects empty identifiers', function () {
    new Student(1, '', '12345');
})->throws(InvalidArgumentException::class);
