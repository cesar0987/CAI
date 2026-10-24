<?php

declare(strict_types=1);

use Cai\Core\Exceptions\DomainException;

it('defines a stable domain exception contract', function () {
    $exception = new class extends DomainException {
        public static function code(): string
        {
            return 'STUDENT_NOT_FOUND';
        }
    };

    expect($exception::code())->toBe('STUDENT_NOT_FOUND');
    expect($exception)->toBeInstanceOf(RuntimeException::class);
});
