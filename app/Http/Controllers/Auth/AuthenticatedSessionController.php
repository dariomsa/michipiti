<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected function redirectPathForUser(): string
    {
        $user = auth()->user();

        if ($user?->hasRole('editor')) {
            return route('editor.productos.index');
        }

        if ($user?->hasRole('disenador')) {
            return route('disenador.productos.index');
        }

        if ($user?->hasRole('disenador_manager')) {
            return route('manager.productos.index');
        }

        if ($user?->hasRole('periodista')) {
            return route('periodista.productos.index');
        }

        return route('dashboard');
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended($this->redirectPathForUser());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
