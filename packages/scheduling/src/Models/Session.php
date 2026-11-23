<?php

declare(strict_types=1);

namespace Cai\Scheduling\Models;

use Cai\Core\Models\Model;
use Cai\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * scheduling.sessions — 03.04 §3.
 * Una sesión grupal = 1 fila aquí + N participantes + N registros de asistencia.
 *
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 */
final class Session extends Model
{
    protected string $schema = 'scheduling';

    protected $table = 'sessions';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // SQLite usa un solo namespace y la tabla `sessions` ya pertenece al
        // driver de sesiones de Laravel; PostgreSQL mantiene `scheduling.sessions`.
        if (DB::getDriverName() === 'sqlite') {
            $this->setTable('scheduling_sessions');
        }
    }

    /** @var list<string> */
    protected $fillable = [
        'service_id', 'group_id', 'session_type', 'starts_at', 'ends_at',
        'status', 'rescheduled_from_id', 'location', 'notes',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsTo<Group, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /** @return HasMany<SessionParticipant, $this> */
    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class);
    }

    /** @return BelongsToMany<Professional, $this> */
    public function professionals(): BelongsToMany
    {
        return $this->belongsToMany(Professional::class, 'session_professionals')
            ->withPivot(['assignment_role', 'starts_at', 'ends_at']);
    }

    /** @return HasManyThrough<AttendanceRecord, SessionParticipant, $this> */
    public function attendanceRecords(): HasManyThrough
    {
        return $this->hasManyThrough(AttendanceRecord::class, SessionParticipant::class, 'session_id', 'participant_id');
    }

    /** @return BelongsTo<Session, $this> */
    public function rescheduledFrom(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'rescheduled_from_id');
    }

    /**
     * Segunda barrera para la agenda (04.03 §3, E-08): los roles con visión
     * global ven todo; un profesional solo sus propias sesiones; el resto
     * falla cerrado hasta tener una asignación que consultar.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user === null || ! $user->isActive()) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('direccion') || $user->hasRole('coordinacion')
            || $user->hasRole('secretaria')) {
            return $query;
        }

        $professionalId = Professional::where('user_id', $user->id)->value('id');
        if ($professionalId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            'professionals',
            fn (Builder $q): Builder => $q->where('professionals.id', $professionalId),
        );
    }
}
