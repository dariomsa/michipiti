<?php

namespace App\Services\Carrusel;

use App\Models\Producto;
use App\Models\User;
use App\Services\Slack\SlackNotificationService;
use Illuminate\Support\Facades\DB;

class CarruselSlackNotifier
{
    public function __construct(
        protected SlackNotificationService $slack,
    ) {
    }

    public function formatHeader(Producto $producto): string
    {
        $titulo = $producto->titulo ?: ('Carrusel #'.$producto->id);
        $responsable = $producto->user?->name ?? 'Sin responsable';

        return "📰 *{$titulo}* (ID: {$producto->id})\n👤 Responsable: {$responsable}";
    }

    /**
     * @return list<int>
     */
    public function involucradosUserIds(Producto $producto): array
    {
        $ids = array_filter([
            $producto->user_id,
            $producto->editor_id,
            $producto->disenador_id,
            $producto->manager_id,
        ]);

        $movIds = DB::table('carrusel_movimientos')
            ->where('carrusel_id', $producto->id)
            ->pluck('user_id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->toArray();

        $msgIds = DB::table('carrusel_mensajes')
            ->where('carrusel_id', $producto->id)
            ->pluck('user_id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->toArray();

        $managerIds = User::role('disenador_manager')->pluck('id')->map(fn ($value) => (int) $value)->toArray();

        return array_values(array_unique(array_map('intval', array_merge($ids, $movIds, $msgIds, $managerIds))));
    }

    public function notifyInvolucrados(Producto $producto, string $texto, ?int $actorUserId = null): void
    {
        $ids = $this->involucradosUserIds($producto);

        if ($actorUserId) {
            $ids = array_values(array_diff($ids, [$actorUserId]));
        }

        if ($ids === []) {
            return;
        }

        User::query()
            ->whereIn('id', $ids)
            ->each(function (User $user) use ($texto): void {
                $this->slack->sendDM($user, $texto);
            });
    }
}
