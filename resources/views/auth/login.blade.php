@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ url('/') }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-900">Inicio</a>
        <a href="{{ route('register') }}" class="text-sm font-medium text-amber-700 transition hover:text-amber-900">Crear cuenta</a>
    </div>

    <div class="grid flex-1 items-center gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-8 shadow-[0_30px_80px_rgba(28,25,23,0.08)] backdrop-blur">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-700">Acceso</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-stone-950">Inicia sesion</h1>
            <p class="mt-4 max-w-lg text-sm leading-6 text-stone-600">
                Accede al panel y trabaja con los roles asignados en tu cuenta.
            </p>
        </section>

        <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-8 text-white shadow-[0_30px_80px_rgba(28,25,23,0.18)]">
            <form action="{{ route('login.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-stone-200">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm text-white outline-none transition focus:border-amber-500" />
                    @error('email') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-stone-200">Password</label>
                    <input id="password" name="password" type="password" class="w-full rounded-2xl border border-stone-700 bg-stone-900 px-4 py-3 text-sm text-white outline-none transition focus:border-amber-500" />
                    @error('password') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-3 text-sm text-stone-300">
                    <input name="remember" type="checkbox" value="1" class="h-4 w-4 rounded border-stone-600 bg-stone-900 text-amber-500" />
                    Recordarme
                </label>

                <button type="submit" class="w-full rounded-full bg-amber-500 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-400">
                    Entrar
                </button>
            </form>
        </section>
    </div>
@endsection
