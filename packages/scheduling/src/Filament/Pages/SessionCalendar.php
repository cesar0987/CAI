<?php

declare(strict_types=1);

namespace Cai\Scheduling\Filament\Pages;

use Cai\Identity\Models\User;
use Cai\Scheduling\Models\Session;
use Filament\Pages\Page;

/**
 * Calendario semanal de la agenda (08.06 §2.7). Se implementa con la tabla
 * de sesiones del panel, sin agregar FullCalendar como dependencia: la vista
 * muestra la semana con sus sesiones y enlaza al recursos para operar.
 */
final class SessionCalendar extends Page
{
    protected static string $view = 'scheduling::filament.pages.session-calendar';

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 55;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->hasPermissionTo('scheduling.sessions.view');
    }

    /**
     * `canView()` solo oculta la navegación: se refuerza en el mount para que
     * la URL directa también respete la matriz 04.02.
     */
    public function mount(): void
    {
        abort_unless(self::canView(), 403);
    }

    /**
     * @return list<array{id: int, title: string, start: string, end: string, status: string}>
     */
    public function getEvents(): array
    {
        $authUser = auth()->user();
        $user = $authUser instanceof User ? $authUser : null;

        return Session::query()
            ->visibleTo($user)
            ->with(['service', 'group'])
            ->whereBetween('starts_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->orderBy('starts_at')
            ->get()
            ->map(fn (Session $session): array => [
                'id' => $session->id,
                'title' => $session->service->name.($session->group !== null ? ' — '.$session->group->name : ''),
                'start' => $session->starts_at->toISOString(),
                'end' => $session->ends_at->toISOString(),
                'status' => $session->status,
            ])
            ->all();
    }
}
