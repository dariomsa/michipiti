<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Support\RoleCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    protected function redirectPathForUser(): string
    {
        $user = auth()->user();

        if ($user?->hasRole('editor')) {
            return route('editor.productos.index');
        }

        if ($user?->hasRole('director')) {
            return route('director.productos.index');
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

        if ($user?->hasRole('videografia')) {
            return route('videografia.audiovisuales.index');
        }

        return route('dashboard');
    }

    public function create(Request $request): View
    {
        abort_unless($request->hasValidSignature(), 403);

        $request->session()->put('register_via_signed_url', true);

        return view('auth.register', [
            'roles' => RoleCatalog::labels(),
        ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        abort_unless($request->session()->pull('register_via_signed_url', false), 403);

        $user = DB::transaction(function () use ($request): User {
            $data = $request->validated();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            foreach ($data['roles'] as $roleName) {
                Role::findOrCreate($roleName, 'web');
            }

            $user->syncRoles($data['roles']);

            return $user;
        });

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->to($this->redirectPathForUser());
    }
}
