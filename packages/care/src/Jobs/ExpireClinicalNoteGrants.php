<?php

declare(strict_types=1);

namespace Cai\Care\Jobs;

use Cai\Care\Models\ClinicalNote;
use Cai\Care\Models\ClinicalNoteAccessGrant;
use Cai\Identity\Audit\Auditor;
use Cai\Identity\Models\SecurityEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

/** 08.07 §2.6: expiracion automatica de grants (E-06). */
final class ExpireClinicalNoteGrants implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ?Auditor $auditor = null) {}

    public function handle(): int
    {
        $auditor = $this->auditor ?? app(Auditor::class);
        $expired = ClinicalNoteAccessGrant::query()
            ->whereNull('revoked_at')
            ->where('expires_at', '<', now())
            ->get();

        DB::connection('care')->transaction(function () use ($expired, $auditor): void {
            foreach ($expired as $grant) {
                $grant->update([
                    'revoked_at' => now(),
                    'revocation_reason' => 'Expiración automática',
                ]);

                SecurityEvent::create([
                    'event_type' => 'grant.expired',
                    'severity' => 'info',
                    'user_id' => $grant->granted_to_user_id,
                    'student_id' => $grant->student_id,
                    'resource_type' => ClinicalNote::class,
                    'resource_id' => $grant->clinical_note_id,
                    'reason' => 'Expiración automática',
                    'created_at' => now(),
                ]);

                $auditor->record(
                    'clinical_note.grant.expired',
                    ClinicalNoteAccessGrant::class,
                    $grant->id,
                    ['student_id' => $grant->student_id],
                    null,
                    $grant->student_id,
                    'warning',
                );
            }
        });

        return $expired->count();
    }
}
