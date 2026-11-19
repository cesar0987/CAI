<?php

declare(strict_types=1);

namespace Cai\Students\Models;

use Cai\Core\Models\Model;
use Database\Factories\GuardianFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * students.guardians — tutor (03.02 §3). Un tutor puede atender alumnos de
 * varias familias; no tiene family_account_id.
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $document_id
 * @property string $status
 */
final class Guardian extends Model
{
    /** @use HasFactory<GuardianFactory> */
    use HasFactory;

    use SoftDeletes;

    protected string $schema = 'students';

    protected $table = 'guardians';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'first_name', 'last_name', 'document_id', 'phone', 'email',
        'address', 'occupation', 'status', 'notes',
    ];

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardians')
            ->withPivot([
                'relationship', 'is_primary', 'can_pick_up',
                'receives_admin_messages', 'is_emergency_contact', 'valid_from', 'valid_until',
            ]);
    }

    protected static function newFactory(): GuardianFactory
    {
        return GuardianFactory::new();
    }
}
