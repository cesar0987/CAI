<?php

declare(strict_types=1);

namespace Cai\Care\Actions;

use Cai\Care\Data\RevokeClinicalNoteGrantData;
use Cai\Care\Exceptions\ClinicalScopeException;
use Cai\Care\Models\ClinicalNote;
use Cai\Care\Models\ClinicalNoteAccessGrant;
use Cai\Core\Support\ModuleConnection;
use Cai\Identity\Audit\Auditor;
use Cai\Identity\Models\SecurityEvent;
use Cai\Identity\Models\User;
use Illuminate\Support\Facades\DB;

/** 04.04 §3.3: revocacion en cualquier momento, con evento de seguridad. */
final class RevokeClinicalNoteGrant
{
    public function __construct(private readonly Auditor $auditor) {}

    public function execute(RevokeClinicalNoteGrantData $data): ClinicalNoteAccessGrant
    {
        $actor = User::findOrFail($data->actorUserId);
        if (! $actor->hasPermissionTo('care.grants.revoke')) {
            throw new ClinicalScopeException('Se necesita care.grants.revoke para revocar un grant.');
        }

        return DB::connection(ModuleConnection::for('care'))->transaction(function () use ($data, $actor): ClinicalNoteAccessGrant {
            $grant = ClinicalNoteAccessGrant::findOrFail($data->grantId);

            if ($grant->revoked_at === null) {
                $grant->update([
                    'revoked_at' => now(),
                    'revoked_by_user_id' => $actor->id,
                    'revocation_reason' => $data->reason,
                ]);

                SecurityEvent::create([
                    'event_type' => 'grant.revoked',
                    'severity' => 'warning',
                    'user_id' => $grant->granted_to_user_id,
                    'student_id' => $grant->student_id,
                    'resource_type' => ClinicalNote::class,
                    'resource_id' => $grant->clinical_note_id,
                    'reason' => $data->reason,
                    'created_at' => now(),
                ]);

                $this->auditor->record(
                    'clinical_note.grant.revoked',
                    ClinicalNoteAccessGrant::class,
                    $grant->id,
                    ['student_id' => $grant->student_id, 'reason' => $data->reason],
                    $actor->id,
                    $grant->student_id,
                    'warning',
                );
            }

            return $grant->fresh();
        });
    }
}
