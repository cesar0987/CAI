<?php

declare(strict_types=1);

namespace Cai\Students\Models;

use Cai\Core\Models\Model;
use Database\Factories\AuthorizedPickupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * students.authorized_pickups — terceros autorizados a retirar (03.02 §7).
 *
 * @property int $id
 * @property int $student_id
 * @property string $full_name
 * @property string $document_id
 * @property Carbon $valid_from
 * @property Carbon|null $valid_until
 * @property bool $is_active
 */
final class AuthorizedPickup extends Model
{
    /** @use HasFactory<AuthorizedPickupFactory> */
    use HasFactory;

    protected string $schema = 'students';

    protected $table = 'authorized_pickups';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'student_id', 'full_name', 'document_id', 'phone', 'relationship',
        'valid_from', 'valid_until', 'is_active', 'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Una autorización vencida o inactiva NO habilita el retiro.
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->valid_until === null || $this->valid_until->isFuture();
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    protected static function newFactory(): AuthorizedPickupFactory
    {
        return AuthorizedPickupFactory::new();
    }
}
