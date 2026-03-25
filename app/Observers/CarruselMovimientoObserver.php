<?php

namespace App\Observers;

use App\Models\CarruselMovimiento;
use App\Models\Producto;
use App\Models\User;
use App\Services\Carrusel\CarruselSlackNotifier;

class CarruselMovimientoObserver
{
    public function created(CarruselMovimiento $movimiento): void
    {
        $producto = Producto::query()->find($movimiento->carrusel_id);

        if (! $producto) {
            return;
        }

        $notifier = app(CarruselSlackNotifier::class);
        $autor = User::query()->find($movimiento->user_id)?->name ?? 'Usuario';
        $accion = ucwords(strtolower(str_replace('_', ' ', (string) ($movimiento->accion ?? 'Movimiento'))));
        $from = ucwords(strtolower(str_replace('_', ' ', (string) ($movimiento->estado_anterior ?? '—'))));
        $to = ucwords(strtolower(str_replace('_', ' ', (string) ($movimiento->estado_nuevo ?? '—'))));
        $motivo = $movimiento->motivo ? "Motivo: {$movimiento->motivo}\n" : '';
        $sep = "────────────────────────\n";

        $texto =
            $sep.
            $notifier->formatHeader($producto)."\n".
            "➡️ {$accion} por {$autor}\n".
            "Estado: {$from} → {$to}\n".
            $motivo.
            $sep;

        $notifier->notifyInvolucrados($producto, $texto);
    }
}
