<?php

declare(strict_types=1);

namespace Cai\Care\Policies;

use Cai\Care\Models\ClinicalNoteAccessGrant;
use Cai\Core\Auth\BasePolicy;
use Cai\Identity\Models\User;

/** 04.04 §3.3: Dirección emite y revoca; los grants no se editan ni se borran. */
final class ClinicalNoteAccessGrantPolicy extends BasePolicy
{
    protected function permissionPrefix(): string
    {
        return 'care.grants';
    }

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'issue') || $this->can($user, 'revoke');
    }

    public function view(User $user, ClinicalNoteAccessGrant $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'issue');
    }

    public function update(User $user, ClinicalNoteAccessGrant $model): bool
    {
        return false;
    }

    public function delete(User $user, ClinicalNoteAccessGrant $model): bool
    {
        return false;
    }

    public function revoke(User $user, ClinicalNoteAccessGrant $model): bool
    {
        return $this->can($user, 'revoke') && $model->isActive();
    }
}
