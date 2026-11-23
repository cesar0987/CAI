<x-filament-panels::page>
    @php($events = $this->getEvents())

    @if($events === [])
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-sm text-gray-600 dark:text-gray-400">No hay sesiones programadas esta semana.</p>
        </div>
    @else
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-4 py-3 text-left">Inicio</th>
                        <th class="px-4 py-3 text-left">Servicio / grupo</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($event['start'])->format('D d/m H:i') }}</td>
                            <td class="px-4 py-3">{{ $event['title'] }}</td>
                            <td class="px-4 py-3">{{ $event['status'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
