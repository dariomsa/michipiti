@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ url('/') }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-900">Inicio</a>
        <a href="{{ route('login') }}" class="text-sm font-medium text-amber-700 transition hover:text-amber-900">Ya tengo cuenta</a>
    </div>

    <div class="grid flex-1 gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-8 text-white shadow-[0_30px_80px_rgba(28,25,23,0.18)]">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-300">Registro</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight">Crear usuario con roles</h1>
            <p class="mt-4 text-sm leading-6 text-stone-300">
                Selecciona uno o varios roles desde el alta inicial. El usuario queda asociado a Spatie Permission desde el registro.
            </p>
        </section>

        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-8 shadow-[0_30px_80px_rgba(28,25,23,0.08)] backdrop-blur">
            <form action="{{ route('register.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-stone-700">Nombre</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500" />
                    @error('name') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-stone-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500" />
                    @error('email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-stone-700">Password</label>
                        <input id="password" name="password" type="password" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500" />
                        @error('password') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-stone-700">Confirmar password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-500" />
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-4">
                        <label class="block text-sm font-medium text-stone-700">Roles</label>
                        <span class="text-xs uppercase tracking-[0.2em] text-stone-400">Multirol</span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($roles as $role => $label)
                            <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 transition hover:border-amber-400 hover:bg-amber-50">
                                <input
                                    name="roles[]"
                                    type="checkbox"
                                    value="{{ $role }}"
                                    @checked(collect(old('roles', []))->contains($role))
                                    class="h-4 w-4 rounded border-stone-300 text-amber-500"
                                />
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('roles') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    @error('roles.*') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-full bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800">
                    Crear cuenta
                </button>
            </form>
        </section>
    </div>
@endsection
