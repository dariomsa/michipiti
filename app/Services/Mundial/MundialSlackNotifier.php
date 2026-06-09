<?php

namespace App\Services\Mundial;

use App\Models\MundialProducto;
use App\Models\User;
use App\Services\Slack\SlackNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MundialSlackNotifier
{
    public function __construct(
        protected SlackNotificationService $slack,
    ) {
    }

    public function formatHeader(MundialProducto $producto): string
    {
        $titulo = $producto->titulo ?: ('Producto Mundial #'.$producto->id);
        $lider = $producto->user?->name ?? 'Sin lider';
        $responsable = $producto->responsable2?->name ?? 'Sin responsable';

        return "*{$titulo}* (Mundial ID: {$producto->id})\nLider: {$lider}\nResponsable: {$responsable}";
    }

    protected function scopeSlackDeliverableUsers($query)
    {
        return $query->where(function ($inner): void {
            $inner->whereNull('email')
                ->orWhere('email', 'not like', '%@demo.com');
        })->where(function ($inner): void {
            $inner->whereNull('email_slack')
                ->orWhere('email_slack', 'not like', '%@demo.com');
        });
    }

    /**
     * @param  list<int|null>  $ids
     * @return list<int>
     */
    protected function expandTeamUserIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return [];
        }

        $teamChildren = User::query()
            ->whereIn('id', $ids)
            ->get(['id', 'usuarios_hijos'])
            ->flatMap(fn (User $user) => collect($user->usuarios_hijos ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        return array_values(array_unique(array_merge($ids, $teamChildren)));
    }

    /**
     * @return list<int>
     */
    public function involucradosUserIds(MundialProducto $producto): array
    {
        $ids = [
            $producto->user_id,
            $producto->responsable2_id,
            $producto->editor_id,
            $producto->manager_id,
        ];

        $movIds = DB::table('mundial_movimientos')
            ->where('mundial_producto_id', $producto->id)
            ->pluck('user_id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->toArray();

        return $this->expandTeamUserIds(array_merge($ids, $movIds));
    }

    public function notifyInvolucrados(MundialProducto $producto, string $texto, ?int $actorUserId = null): void
    {
        $ids = $this->involucradosUserIds($producto);

        if ($actorUserId) {
            $ids = array_values(array_diff($ids, [$actorUserId]));
        }

        if ($ids === []) {
            return;
        }

        $users = $this->scopeSlackDeliverableUsers(User::query())
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'email', 'email_slack', 'slack_user_id']);

        Log::info('[SLACK DEBUG] destinatarios movimiento/mundial', [
            'mundial_producto_id' => $producto->id,
            'titulo' => $producto->titulo,
            'actor_user_id' => $actorUserId,
            'destinatarios' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_slack' => $user->email_slack,
                'slack_user_id' => $user->slack_user_id,
            ])->values()->all(),
            'mensaje' => $texto,
        ]);

        $users->each(function (User $user) use ($texto): void {
            $this->slack->sendDM($user, $texto);
        });
    }

    public function mentionsInvolucrados(MundialProducto $producto): string
    {
        $ids = $this->involucradosUserIds($producto);

        if ($ids === []) {
            return '';
        }

        $users = $this->scopeSlackDeliverableUsers(User::query())
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'email', 'email_slack', 'slack_user_id']);

        $mentions = $users
            ->map(function (User $user): ?string {
                $email = $user->email_slack ?: $user->email;
                $slackUserId = $user->slack_user_id ?: $this->slack->userIdByEmail($email);

                if (! $slackUserId) {
                    return null;
                }

                if (! $user->slack_user_id) {
                    $user->slack_user_id = $slackUserId;
                    $user->save();
                }

                return "<@{$slackUserId}>";
            })
            ->filter()
            ->unique()
            ->values();

        return $mentions->join(' ');
    }
}
