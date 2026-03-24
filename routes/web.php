<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Disenador\ProductoController as DisenadorProductoController;
use App\Http\Controllers\Editor\ProductoController as EditorProductoController;
use App\Http\Controllers\Manager\ProductoController as ManagerProductoController;
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

    if ($user->hasRole('editor')) {
        return redirect()->route('editor.productos.index');
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

    if ($user->hasRole('videografia')) {
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

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/pauta', [PautaController::class, 'index'])->name('pauta.index');
    Route::get('/pauta/items', [PautaController::class, 'items'])->name('pauta.items');
    Route::post('/pauta/{id}/programar', [PautaController::class, 'programar'])->name('pauta.programar');
    Route::get('/planificador', [PlanificadorController::class, 'index'])->name('planificador');
    Route::get('/planificador/week', [PlanificadorController::class, 'week']);
    Route::get('/planificador/periodistas', [PlanificadorController::class, 'periodistas']);
    Route::post('/planificador/store', [PlanificadorController::class, 'store']);
    Route::post('/planificador/move', [PlanificadorController::class, 'move']);
    Route::post('/planificador/aprobar', [PlanificadorController::class, 'approve']);
    Route::post('/planificador/to-pauta', [PlanificadorController::class, 'toPauta']);
    Route::delete('/planificador/{producto}', [PlanificadorController::class, 'destroy']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::prefix('periodista')
    ->name('periodista.')
    ->middleware(['auth', 'role:periodista'])
    ->group(function (): void {
        Route::get('/productos', [PeriodistaProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/create', [PeriodistaProductoController::class, 'create'])->name('productos.create');
        Route::get('/productos/{producto}/edit', [PeriodistaProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [PeriodistaProductoController::class, 'update'])->name('productos.update');
        Route::post('/productos/{producto}/mensajes', [PeriodistaProductoController::class, 'storeMessage'])->name('productos.mensajes.store');
    });

Route::prefix('editor')
    ->name('editor.')
    ->middleware(['auth', 'role:editor'])
    ->group(function (): void {
        Route::get('/productos', [EditorProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/create', [EditorProductoController::class, 'create'])->name('productos.create');
        Route::get('/productos/{producto}/edit', [EditorProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [EditorProductoController::class, 'update'])->name('productos.update');
        Route::post('/productos/{producto}/mensajes', [EditorProductoController::class, 'storeMessage'])->name('productos.mensajes.store');
    });

Route::prefix('disenador')
    ->name('disenador.')
    ->middleware(['auth', 'role:disenador'])
    ->group(function (): void {
        Route::get('/productos', [DisenadorProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/create', [DisenadorProductoController::class, 'create'])->name('productos.create');
        Route::get('/productos/{producto}/edit', [DisenadorProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [DisenadorProductoController::class, 'update'])->name('productos.update');
        Route::post('/productos/{producto}/mensajes', [DisenadorProductoController::class, 'storeMessage'])->name('productos.mensajes.store');
    });

Route::prefix('manager')
    ->name('manager.')
    ->middleware(['auth', 'role:disenador_manager'])
    ->group(function (): void {
        Route::get('/productos', [ManagerProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/create', [ManagerProductoController::class, 'create'])->name('productos.create');
        Route::get('/productos/{producto}/edit', [ManagerProductoController::class, 'edit'])->name('productos.edit');
        Route::put('/productos/{producto}', [ManagerProductoController::class, 'update'])->name('productos.update');
        Route::post('/productos/{producto}/mensajes', [ManagerProductoController::class, 'storeMessage'])->name('productos.mensajes.store');
    });

Route::prefix('videografia')
    ->name('videografia.')
    ->middleware(['auth', 'role:videografia,editor,director'])
    ->group(function (): void {
        Route::get('/listado', [VideografiaAudiovisualController::class, 'index'])->name('audiovisuales.index');
        Route::get('/listado/{audiovisual}/edit', [VideografiaAudiovisualController::class, 'edit'])->name('audiovisuales.edit');
        Route::put('/listado/{audiovisual}', [VideografiaAudiovisualController::class, 'update'])->name('audiovisuales.update');
        Route::post('/listado/{audiovisual}/mensajes', [VideografiaAudiovisualController::class, 'storeMessage'])->name('audiovisuales.mensajes.store');
        Route::get('/planificacion', [VideografiaPlanificadorController::class, 'index'])->name('audiovisuales.planificacion');
        Route::get('/planificacion/week', [VideografiaPlanificadorController::class, 'week']);
        Route::get('/planificacion/responsables', [VideografiaPlanificadorController::class, 'responsables']);
        Route::post('/planificacion/store', [VideografiaPlanificadorController::class, 'store']);
        Route::post('/planificacion/move', [VideografiaPlanificadorController::class, 'move']);
        Route::post('/planificacion/aprobar', [VideografiaPlanificadorController::class, 'approve']);
        Route::post('/planificacion/to-pauta', [VideografiaPlanificadorController::class, 'toPauta']);
        Route::delete('/planificacion/{audiovisual}', [VideografiaPlanificadorController::class, 'destroy']);
    });
