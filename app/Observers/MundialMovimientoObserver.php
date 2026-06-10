<?php

namespace App\Observers;

use App\Models\MundialMovimiento;
use App\Models\User;
use App\Services\Mundial\MundialSlackNotifier;
use Carbon\Carbon;

class MundialMovimientoObserver
{
    private const SLACK_MOVIMIENTOS_USER_ID = 1;

    public function created(MundialMovimiento $movimiento): void
    {
        $producto = $movimiento->mundialProducto()
            ->with(['user:id,name', 'responsable2:id,name'])
            ->first();

        if (! $producto) {
            return;
        }

        $notifier = app(MundialSlackNotifier::class);
        $autor = $movimiento->user?->name ?? 'Usuario';
        $accion = ucwords(strtolower(str_replace('_', ' ', (string) ($movimiento->accion ?? 'Movimiento'))));
        $mentions = $notifier->mentionsInvolucrados($producto);
        $heading = trim(':trophy: Especial Mundial '.$mentions);
        $motivo = $movimiento->motivo ? "Motivo: {$movimiento->motivo}\n" : '';
        $fecha = optional($producto->fecha)->format('Y-m-d') ?: 'Sin fecha';
        $hora = $producto->hora ? Carbon::parse($producto->hora)->format('H:i') : 'Sin hora';
        $sep = "------------------------\n";

        $texto =
            $heading."\n".
            $sep.
            $notifier->formatHeader($producto)."\n".
            "{$accion} por {$autor}\n".
            $motivo.
            "Publicacion: {$fecha} {$hora}\n".
            $sep;

        // $notifier->notifyInvolucrados($producto, $texto, $movimiento->user_id ? (int) $movimiento->user_id : null);

        $destinatario = User::query()->find(self::SLACK_MOVIMIENTOS_USER_ID);

        if ($destinatario) {
            app(\App\Services\Slack\SlackNotificationService::class)->sendDM($destinatario, $texto);
        }
    }
}
