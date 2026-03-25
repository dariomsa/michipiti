@extends('layouts.auth')

@section('title', 'Login')

@section('content')
   
    <div class="grid flex-1 items-center gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="rounded-[2rem] border border-white/70 bg-white/85 p-8 shadow-[0_30px_80px_rgba(28,25,23,0.08)] backdrop-blur">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-700">version 2.0</p>
     <div class="card-header text-center">
        <img src="https://michipiti.elcomercio.com/images/chatmichipiti-logo.svg" alt="Chat Michipiti" style="max-width:220px; width:100%; height:auto;">
          </div>
      
        </section>

        <section class="rounded-[2rem] border border-stone-200 bg-white/85 p-8  shadow-[0_30px_80px_rgba(28,25,23,0.08)]">
            <form action="{{ route('login.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-amber-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-stone-700 px-4 py-3 text-sm ext-white outline-none transition focus:border-amber-500" />
                    @error('email') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-amber-700">Password</label>
                    <input id="password" name="password" type="password" class="w-full rounded-2xl border border-stone-700 px-4 py-3 text-smtext-white outline-none transition focus:border-amber-500" />
                    @error('password') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>

              

                <button type="submit" class="w-full rounded-full bg-amber-500 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-400">
                    Entrar
                </button>
            </form>
        </section>
    </div>
@endsection
