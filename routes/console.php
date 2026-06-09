<?php

use App\Models\MundialProducto;
use App\Models\User;
use App\Services\Mundial\MundialSlackNotifier;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mundial:notificar-importados {--send : Envia los mensajes; sin esta opcion solo simula} {--id= : Notifica un producto Mundial especifico} {--limit= : Limita la cantidad de productos} {--offset= : Salta una cantidad de productos ordenados por fecha/hora/id} {--sleep=0 : Segundos de espera entre envios reales} {--visible-only : Solo productos visibles}', function () {
    $send = (bool) $this->option('send');
    $id = $this->option('id');
    $limit = $this->option('limit');
    $offset = $this->option('offset');
    $sleep = max(0, (int) $this->option('sleep'));
    $visibleOnly = (bool) $this->option('visible-only');

    $query = MundialProducto::query()
        ->with([
            'user:id,name',
            'responsable2:id,name',
            'manager:id,name',
            'tipoProducto:id,nombre,slug',
            'mundialPrioridad:id,nombre',
            'mundialEquipo:id,nombre',
            'mundialTipo:id,nombre',
        ])
        ->orderBy('fecha')
        ->orderBy('hora')
        ->orderBy('id');

    if ($id) {
        $query->whereKey((int) $id);
    }

    if ($visibleOnly) {
        $query->where('visible', true);
    }

    if ($limit) {
        $query->limit((int) $limit);
    }

    if ($offset) {
        $query->offset((int) $offset);
    }

    $notifier = app(MundialSlackNotifier::class);
    $productos = $query->get();

    if ($productos->isEmpty()) {
        $this->warn('No hay productos Mundial para notificar.');

        return self::SUCCESS;
    }

    $this->info(($send ? 'Enviando' : 'Simulando').' notificacion para '.$productos->count().' productos Mundial.');

    $enviados = 0;
    $sinDestinatarios = 0;

    foreach ($productos as $producto) {
        $ids = $notifier->involucradosUserIds($producto);

        if ($ids === []) {
            $sinDestinatarios++;
            $this->line("SIN DESTINATARIOS Mundial #{$producto->id}: {$producto->titulo}");
            continue;
        }

        $mentions = $notifier->mentionsInvolucrados($producto);
        $heading = trim(':trophy: Especial Mundial '.$mentions);
        $fecha = optional($producto->fecha)->format('Y-m-d') ?: 'Sin fecha';
        $hora = $producto->hora ? \Carbon\Carbon::parse($producto->hora)->format('H:i') : 'Sin hora';

        $texto =
            $heading."\n".
            "------------------------\n".
            $notifier->formatHeader($producto)."\n".
            "Producto en Especial Mundial.\n".
            "Horario: {$fecha} {$hora}\n".
            "------------------------\n";

        if ($send) {
            $notifier->notifyInvolucrados($producto, $texto);

            $destinatario = User::query()->find(1);
            if ($destinatario) {
                app(\App\Services\Slack\SlackNotificationService::class)->sendDM($destinatario, $texto);
            }

            $enviados++;
            $this->line("ENVIADO Mundial #{$producto->id}: {$producto->titulo}");
            if ($sleep > 0) {
                sleep($sleep);
            }
            continue;
        }

        $this->line("DRY-RUN Mundial #{$producto->id}: {$producto->titulo} -> ".count($ids).' involucrados');
    }

    $this->info("Listo. Procesados: {$productos->count()}. Enviados: {$enviados}. Sin destinatarios: {$sinDestinatarios}.");

    if (! $send) {
        $this->comment('Para enviar realmente, ejecuta con --send.');
    }

    return self::SUCCESS;
})->purpose('Notifica por Slack los productos Mundial importados inicialmente');
