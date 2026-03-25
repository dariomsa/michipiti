<?php

namespace App\Observers;

use App\Models\CarruselMensaje;
use App\Models\Producto;
use App\Models\User;
use App\Services\Carrusel\CarruselSlackNotifier;

class CarruselMensajeObserver
{
    public function created(CarruselMensaje $mensaje): void
    {
        $producto = Producto::query()->find($mensaje->carrusel_id);

        if (! $producto) {
            return;
        }

        $notifier = app(CarruselSlackNotifier::class);
        $autor = User::query()->find($mensaje->user_id)?->name ?? 'Usuario';
        $contenido = trim((string) ($mensaje->mensaje ?? ''));

        if ($contenido === '') {
            $contenido = '(sin contenido)';
        }

        if (function_exists('mb_strlen') && mb_strlen($contenido) > 500) {
            $contenido = mb_substr($contenido, 0, 500).'...';
        } elseif (strlen($contenido) > 500) {
            $contenido = substr($contenido, 0, 500).'...';
        }

        $replyInfo = $mensaje->reply_to_id ? "↪ Respuesta a mensaje ID: {$mensaje->reply_to_id}\n" : '';
        $sep = "────────────────────────\n";

        $texto =
            $sep.
            $notifier->formatHeader($producto)."\n".
            "💬 *{$autor}* comentó:\n".
            $replyInfo.
            $contenido."\n".
            $sep;

        $notifier->notifyInvolucrados($producto, $texto);
    }
}
