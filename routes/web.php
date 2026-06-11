<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Disenador\ProductoController as DisenadorProductoController;
use App\Http\Controllers\Director\ProductoController as DirectorProductoController;
use App\Http\Controllers\Director\DashboardController as DirectorDashboardController;
use App\Http\Controllers\EmpresaActivaController;
use App\Http\Controllers\Editor\ProductoController as EditorProductoController;
use App\Http\Controllers\CalendarioEspecialController;
use App\Http\Controllers\HorarioSlotController;
use App\Http\Controllers\Manager\ProductoController as ManagerProductoController;
use App\Http\Controllers\Mundial\ProductoController as MundialProductoController;
use App\Http\Controllers\Mundial\PlanificadorController as MundialPlanificadorController;
use App\Http\Controllers\PautaController;
use App\Http\Controllers\PlanificadorController;
use App\Http\Controllers\Periodista\ProductoController as PeriodistaProductoController;
use App\Http\Controllers\Videografia\AudiovisualController as VideografiaAudiovisualController;
use App\Http\Controllers\Videografia\PlanificadorController as VideografiaPlanificadorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->hasRole('mundial_lectura') && $user->getRoleNames()->count() === 1) {
        return redirect()->route('mundial.index');
    }

    if ($user->hasRole('editor')) {
        return redirect()->route('editor.productos.index');
    }

    if ($user->hasRole('director')) {
        return redirect()->route('director.productos.index');
    }

    if ($user->hasRole('disenador')) {
        return redirect()->route('disenador.productos.index');
    }

    if ($user->hasRole('disenador_manager')) {
        return redirect()->route('manager.productos.index');
    }

    if ($user->hasRole('periodista')) {
        return redirect()->route('periodista.productos.index');
    }

    if ($user->hasAnyRole(['videografia', 'video_manager'])) {
        return redirect()->route('videografia.audiovisuales.index');
    }

    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware(['auth', 'empresa.activa'])->group(function (): void {
    Route::post('/empresa-activa', [EmpresaActivaController::class, 'update'])->name('empresa-activa.update');
    Route::get('/mundial/listado', [MundialProductoController::class, 'index'])->name('mundial.index');
    Route::post('/mundial/listado/{producto}/metricool', [MundialProductoController::class, 'metricool'])->name('mundial.metricool');
    Route::get('/mundial/planificador', [MundialPlanificadorController::class, 'index'])->name('mundial.planificador');
    Route::get('/mundial/planificador/week', [MundialPlanificadorController::class, 'week']);
    Route::get('/mundial/planificador/periodistas', [MundialPlanificadorController::class, 'periodistas']);
    Route::get('/mundial/planificador/videografos', [MundialPlanificadorController::class, 'videografos']);
    Route::post('/mundial/planificador/store', [MundialPlanificadorController::class, 'store']);
    Route::post('/mundial/planificador/move', [MundialPlanificadorController::class, 'move']);
    Route::post('/mundial/planificador/aprobar', [MundialPlanificadorController::class, 'approve']);
    Route::post('/mundial/planificador/to-pauta', [MundialPlanificadorController::class, 'toPauta']);
    Route::delete('/mundial/planificador/{producto}', [MundialPlanificadorController::class, 'destroy']);

    Route::middleware('not.mundial_readonly')->group(function (): void {
        Route::get('/dashboard', [DirectorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pauta', [PautaController::class, 'index'])->name('pauta.index');
        Route::get('/pauta/items', [PautaController::class, 'items'])->name('pauta.items');
        Route::post('/pauta/{id}/programar', [PautaController::class, 'programar'])->name('pauta.programar');
        Route::post('/pauta/{id}/metricool', [PautaController::class, 'metricool'])->name('pauta.metricool');
        Route::get('/planificador', [PlanificadorController::class, 'index'])->name('planificador');
        Route::get('/planificador/horarios', [PlanificadorController::class, 'horarios'])->name('planificador.horarios');
        Route::get('/planificador/week', [PlanificadorController::class, 'week']);
        Route::get('/planificador/periodistas', [PlanificadorController::class, 'periodistas']);
        Route::post('/planificador/store', [PlanificadorController::class, 'store']);
        Route::post('/planificador/move', [PlanificadorController::class, 'move']);
        Route::post('/planificador/aprobar', [PlanificadorController::class, 'approve']);
        Route::post('/planificador/to-pauta', [PlanificadorController::class, 'toPauta']);
        Route::post('/planificador/mundial-to-pauta', [PlanificadorController::class, 'mundialToPauta']);
        Route::delete('/planificador/{producto}', [PlanificadorController::class, 'destroy']);
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'role:director'])->group(function (): void {
    Route::get('/horario-slots', [HorarioSlotController::class, 'index'])->name('horario-slots.index');
    Route::patch('/horario-slots/{horarioSlot}', [HorarioSlotController::class, 'update'])->name('horario-slots.update');
    Route::get('/calendario-especial', [CalendarioEspecialController::class, 'index'])->name('calendario-especial.index');
    Route::post('/calendario-especial', [CalendarioEspecialController::class, 'store'])->name('calendario-especial.store');
    Route::put('/calendario-especial/{calendarioEspecial}', [CalendarioEspecialController::class, 'update'])->name('calendario-especial.update');
    Route::delete('/calendario-especial/{calendarioEspecial}', [CalendarioEspecialController::class, 'destroy'])->name('calendario-especial.destroy');
});

Route::prefix('periodista')
    ->name('periodista.')
    ->middleware(['auth', 'empresa.activa', 'role:periodista'])
    ->group(function (): void {
        Route::get('/productos', [PeriodistaProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/create', [PeriodistaProductoController::class, 'create'])->name('productos.create');
        Route::get('/productos/{producto}', fn ($producto) => redirect()->route('periodista.productos.edit', $producto))->name('productos.show');
        Route::get('/productos/{producto}/edit', [PeriodistaProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [PeriodistaProductoController::class, 'update'])->name('productos.update');
        Route::patch('/productos/{producto}/autosave', [PeriodistaProductoController::class, 'autosave'])->name('productos.autosave');
        Route::post('/productos/{producto}/mensajes', [PeriodistaProductoController::class, 'storeMessage'])->name('productos.mensajes.store');
    });

Route::prefix('editor')
    ->name('editor.')
    ->middleware(['auth', 'empresa.activa', 'role:editor'])
    ->group(function (): void {
        Route::get('/productos', [EditorProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/create', [EditorProductoController::class, 'create'])->name('productos.create');
        Route::get('/productos/{producto}', fn ($producto) => redirect()->route('editor.productos.edit', $producto))->name('productos.show');
        Route::get('/productos/{producto}/edit', [EditorProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [EditorProductoController::class, 'update'])->name('productos.update');
        Route::patch('/productos/{producto}/autosave', [EditorProductoController::class, 'autosave'])->name('productos.autosave');
        Route::post('/productos/{producto}/approve', [EditorProductoController::class, 'approve'])->name('productos.approve');
        Route::post('/productos/{producto}/mensajes', [EditorProductoController::class, 'storeMessage'])->name('productos.mensajes.store');
    });

Route::prefix('director')
    ->name('director.')
    ->middleware(['auth', 'empresa.activa', 'role:director'])
    ->group(function (): void {
        Route::get('/productos', [DirectorProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/create', [DirectorProductoController::class, 'create'])->name('productos.create');
        Route::get('/productos/{producto}', fn ($producto) => redirect()->route('director.productos.edit', $producto))->name('productos.show');
        Route::get('/productos/{producto}/edit', [DirectorProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [DirectorProductoController::class, 'update'])->name('productos.update');
        Route::patch('/productos/{producto}/autosave', [DirectorProductoController::class, 'autosave'])->name('productos.autosave');
        Route::post('/productos/{producto}/approve', [DirectorProductoController::class, 'approve'])->name('productos.approve');
        Route::post('/productos/{producto}/mensajes', [DirectorProductoController::class, 'storeMessage'])->name('productos.mensajes.store');
    });

Route::prefix('disenador')
    ->name('disenador.')
    ->middleware(['auth', 'empresa.activa', 'role:disenador'])
    ->group(function (): void {
        Route::get('/productos', [DisenadorProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/create', [DisenadorProductoController::class, 'create'])->name('productos.create');
        Route::get('/productos/{producto}', fn ($producto) => redirect()->route('disenador.productos.edit', $producto))->name('productos.show');
        Route::get('/productos/{producto}/edit', [DisenadorProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [DisenadorProductoController::class, 'update'])->name('productos.update');
        Route::patch('/productos/{producto}/autosave', [DisenadorProductoController::class, 'autosave'])->name('productos.autosave');
        Route::post('/productos/{producto}/mensajes', [DisenadorProductoController::class, 'storeMessage'])->name('productos.mensajes.store');
    });

Route::prefix('manager')
    ->name('manager.')
    ->middleware(['auth', 'empresa.activa', 'role:disenador_manager'])
    ->group(function (): void {
        Route::get('/productos', [ManagerProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/create', [ManagerProductoController::class, 'create'])->name('productos.create');
        Route::get('/productos/{producto}', fn ($producto) => redirect()->route('manager.productos.edit', $producto))->name('productos.show');
        Route::get('/productos/{producto}/edit', [ManagerProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [ManagerProductoController::class, 'update'])->name('productos.update');
        Route::patch('/productos/{producto}/autosave', [ManagerProductoController::class, 'autosave'])->name('productos.autosave');
        Route::post('/productos/{producto}/mensajes', [ManagerProductoController::class, 'storeMessage'])->name('productos.mensajes.store');
    });

Route::prefix('videografia')
    ->name('videografia.')
    ->middleware(['auth', 'empresa.activa', 'role:videografia,video_manager,editor,director'])
    ->group(function (): void {
        Route::get('/listado', [VideografiaAudiovisualController::class, 'index'])->name('audiovisuales.index');
        Route::get('/listado/create', [VideografiaAudiovisualController::class, 'create'])->name('audiovisuales.create');
        Route::post('/listado', [VideografiaAudiovisualController::class, 'store'])->name('audiovisuales.store');
        Route::get('/listado/{audiovisual}/edit', [VideografiaAudiovisualController::class, 'edit'])->name('audiovisuales.edit');
        Route::put('/listado/{audiovisual}', [VideografiaAudiovisualController::class, 'update'])->name('audiovisuales.update');
        Route::delete('/listado/{audiovisual}/slack-media', [VideografiaAudiovisualController::class, 'destroySlackMedia'])->name('audiovisuales.slack-media.destroy');
        Route::post('/listado/{audiovisual}/mensajes', [VideografiaAudiovisualController::class, 'storeMessage'])->name('audiovisuales.mensajes.store');
        Route::get('/planificacion', [VideografiaPlanificadorController::class, 'index'])->name('audiovisuales.planificacion');
        Route::get('/multimedia', [VideografiaAudiovisualController::class, 'multimedia'])->name('audiovisuales.multimedia');
        Route::get('/planificacion/week', [VideografiaPlanificadorController::class, 'week']);
        Route::get('/planificacion/responsables', [VideografiaPlanificadorController::class, 'responsables']);
        Route::post('/planificacion/store', [VideografiaPlanificadorController::class, 'store']);
        Route::post('/planificacion/move', [VideografiaPlanificadorController::class, 'move']);
        Route::post('/planificacion/aprobar', [VideografiaPlanificadorController::class, 'approve']);
        Route::post('/planificacion/to-pauta', [VideografiaPlanificadorController::class, 'toPauta']);
        Route::delete('/planificacion/{audiovisual}', [VideografiaPlanificadorController::class, 'destroy']);
    });
