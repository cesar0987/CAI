<?php

declare(strict_types=1);

namespace Cai\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait SoftDeletesByUser
{
    protected static function bootSoftDeletesByUser(): void
    {
        static::deleting(function (Model $model): void {
            $model->markDeletedBy();
        });
    }

    public function markDeletedBy(?int $userId = null): void
    {
        $resolvedUserId = $userId ?? Auth::id();

        if ($resolvedUserId === null) {
            return;
        }

        $this->setAttribute('deleted_by', $resolvedUserId);
    }
}
