<?php

declare(strict_types=1);

namespace Cai\Students\Events;

use Cai\Core\Events\DomainEvent;

/**
 * Evento de transferencia de alumno entre cuentas (03.02 §6.2).
 * Payload escalar: sin modelos Eloquent (frontera A-03).
 */
final class StudentTransferredToAccount extends DomainEvent
{
    public function __construct(int $studentId, int $fromAccountId, int $toAccountId, ?string $reason = null)
    {
        parent::__construct('students.transferred_to_account', [
            'student_id' => $studentId,
            'from_account_id' => $fromAccountId,
            'to_account_id' => $toAccountId,
            'reason' => $reason,
        ]);
    }
}
