<?php

declare(strict_types=1);

namespace Cai\Care\Policies;

use Cai\Care\Authorization\ClinicalScope;
use Cai\Care\Enums\ClinicalNoteStatus;
use Cai\Care\Models\ClinicalNote;
use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;

/**
 * 04.03 §2 / 08.07 §2.5. Capa 1: permiso. Capa 2: autor, grant vigente o
 * asignación/responsabilidad de plan sobre el alumno a la fecha de la nota.
 */
final class ClinicalNotePolicy extends BasePolicy
{
    public function __construct(private readonly ClinicalScope $scope) {}

    protected function permissionPrefix(): string
    {
        return 'care.notes';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'view');
    }

    public function view(User $user, ClinicalNote $note): bool
    {
        if (! $this->can($user, 'view')) {
            return false;
        }

        if ($note->author_user_id === $user->id || $note->hasActiveGrantFor($user->id)) {
            return true;
        }

        return $this->scope->hasActiveAssignmentFor($user, $note->student_id, $note->recorded_at)
            || $this->scope->hasPlanResponsibilityFor($user, $note->student_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'create') && $this->scope->hasActiveProfessionalProfile($user);
    }

    public function update(User $user, ClinicalNote $note): bool
    {
        return $note->status === ClinicalNoteStatus::Draft->value
            && $note->author_user_id === $user->id
            && $this->can($user, 'create');
    }

    public function finalize(User $user, ClinicalNote $note): bool
    {
        return $note->status === ClinicalNoteStatus::Draft->value
            && $note->author_user_id === $user->id
            && $this->can($user, 'finalize');
    }

    public function supersede(User $user, ClinicalNote $note): bool
    {
        if (! $this->can($user, 'supersede') || $note->status === ClinicalNoteStatus::Draft->value) {
            return false;
        }

        return $note->author_user_id === $user->id
            || $this->scope->hasActiveAssignmentFor($user, $note->student_id, $note->recorded_at);
    }

    public function delete(User $user, ClinicalNote $note): bool
    {
        // Las notas nunca se borran: se corrigen con una nota nueva (04.04 §7).
        return false;
    }

    public function restore(User $user, ClinicalNote $note): bool
    {
        return false;
    }

    public function forceDelete(User $user, ClinicalNote $note): bool
    {
        return false;
    }
}
