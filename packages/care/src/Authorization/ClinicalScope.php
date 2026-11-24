<?php

declare(strict_types=1);

namespace Cai\Care\Authorization;

use Cai\Care\Models\CarePlan;
use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Professional;
use Cai\Scheduling\Models\ProfessionalAssignment;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Capa 2 de autorización (04.03): alcance por registro sobre el alumno.
 *
 * La asignación se evalúa a la fecha de la nota, no a la fecha actual, para
 * que el profesional conserve el acceso a su trabajo histórico (04.03 §9).
 * `User` no implementa `hasActiveClinicalAssignmentFor()`: se resuelve acá
 * para no ensuciar identity con reglas de scheduling/care (Deptrac).
 */
final class ClinicalScope
{
    public function hasActiveProfessionalProfile(User $user): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return Professional::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function hasActiveAssignmentFor(User $user, int $studentId, ?DateTimeInterface $at = null): bool
    {
        $professionalId = $this->professionalIdFor($user);
        if ($professionalId === null) {
            return false;
        }

        $date = ($at ?? now())->format('Y-m-d');

        return ProfessionalAssignment::query()
            ->where('professional_id', $professionalId)
            ->where('student_id', $studentId)
            ->where('valid_from', '<=', $date)
            ->where(function (Builder $query): void {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString());
            })
            ->exists();
    }

    /**
     * Alumnos alcanzables por asignación vigente o responsabilidad de plan.
     * Lo usa el scope de listado para que fila y detalle apliquen el mismo alcance.
     *
     * @return list<int>
     */
    public function scopedStudentIds(User $user): array
    {
        $professionalId = $this->professionalIdFor($user);
        if ($professionalId === null) {
            return [];
        }

        $today = now()->toDateString();
        $assigned = ProfessionalAssignment::query()
            ->where('professional_id', $professionalId)
            ->where('valid_from', '<=', $today)
            ->where(function (Builder $query) use ($today): void {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', $today);
            })
            ->pluck('student_id')
            ->all();

        $plans = CarePlan::query()
            ->where('responsible_professional_id', $professionalId)
            ->pluck('student_id')
            ->all();

        return array_values(array_unique(array_map(static fn (mixed $id): int => (int) $id, [...$assigned, ...$plans])));
    }

    /**
     * 04.04 §3.2: el responsable del plan también ve las notas del alumno.
     */
    public function hasPlanResponsibilityFor(User $user, int $studentId): bool
    {
        $professionalId = $this->professionalIdFor($user);
        if ($professionalId === null) {
            return false;
        }

        return CarePlan::query()
            ->where('student_id', $studentId)
            ->where(function (Builder $query) use ($professionalId): void {
                $query->where('responsible_professional_id', $professionalId)
                    ->orWhereHas('professionals', fn (Builder $professionals): Builder => $professionals->whereKey($professionalId));
            })
            ->exists();
    }

    private function professionalIdFor(User $user): ?int
    {
        if (! $user->isActive()) {
            return null;
        }

        $id = Professional::query()->where('user_id', $user->id)->value('id');

        return $id === null ? null : (int) $id;
    }
}
