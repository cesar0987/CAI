<?php

declare(strict_types=1);

namespace Cai\Students\Actions;

use Cai\Identity\Audit\Auditor;
use Cai\Students\Models\FamilyAccountMember;
use Illuminate\Support\Facades\DB;

/**
 * Transferencia de alumno entre cuentas familiares (03.02 §6.2).
 *
 * En una sola transacción: cierra la membresía vigente (no la borra) y crea
 * la nueva. La restricción de exclusión por rango es PostgreSQL-only; en
 * SQLite esta operación serializada es la que impide dos cuentas activas.
 */
final class TransferFamilyMembership
{
    public function __construct(private readonly Auditor $auditor) {}

    public function execute(int $studentId, int $newAccountId, ?string $reason = null): void
    {
        DB::transaction(function () use ($studentId, $newAccountId, $reason): void {
            $current = FamilyAccountMember::where('student_id', $studentId)
                ->whereNull('valid_until')
                ->lockForUpdate()
                ->first();

            if ($current !== null && $current->family_account_id === $newAccountId) {
                throw new \InvalidArgumentException('El alumno ya pertenece a esa cuenta.');
            }

            $fromAccountId = $current?->family_account_id;

            if ($current !== null) {
                $current->update(['valid_until' => now()->toDateString()]);
            }

            $membership = FamilyAccountMember::create([
                'family_account_id' => $newAccountId,
                'student_id' => $studentId,
                'role' => 'dependiente',
                'valid_from' => now()->toDateString(),
                'valid_until' => null,
            ]);

            $this->auditor->record('students.transferred_to_account', FamilyAccountMember::class, $membership->id, [
                'student_id' => $studentId,
                'from_account_id' => $fromAccountId,
                'to_account_id' => $newAccountId,
                'reason' => $reason,
            ], null, $studentId, 'warning');
        });
    }
}
