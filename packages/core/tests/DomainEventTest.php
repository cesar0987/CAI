<?php

declare(strict_types=1);

use Cai\Core\Events\DomainEvent;

it('keeps events as plain serializable payloads', function () {
    $event = new class extends DomainEvent {
        public function __construct()
        {
            parent::__construct('student.enrolled', [
                'student_id' => 42,
                'status' => 'active',
                'meta' => [
                    'source' => 'import',
                ],
            ]);
        }
    };

    expect($event->eventName())->toBe('student.enrolled');
    expect($event->payload()['student_id'])->toBe(42);
    expect($event->toArray()['payload']['meta']['source'])->toBe('import');
    expect($event->toArray()['event'])->toBe('student.enrolled');
});

it('serializes dates into an iso string', function () {
    $event = new class extends DomainEvent {
        public function __construct()
        {
            parent::__construct('payment.registered', [
                'paid_at' => new DateTimeImmutable('2026-10-23 10:00:00'),
            ]);
        }
    };

    expect($event->toArray()['payload']['paid_at'])->toBe('2026-10-23T10:00:00+00:00');
});
