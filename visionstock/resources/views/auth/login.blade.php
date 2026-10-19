@extends('layouts.guest', ['title' => 'Masuk'])

@section('content')
    <main class="grid min-h-dvh lg:grid-cols-[47.2fr_52.8fr]">
        <div class="relative hidden min-h-dvh overflow-hidden lg:block">
            <img class="absolute inset-0 size-full object-cover" src="{{ asset('images/image-login-regis.png') }}" alt="Rak persediaan di toko">
        </div>

        <section class="flex min-h-dvh items-center justify-center px-4 py-10 sm:px-8 lg:px-8 xl:px-12">
            <div class="w-full max-w-[632px]">
                <a class="mb-8 inline-block min-h-11 content-center text-sm font-semibold text-brand-ink/75 hover:text-brand-ink" href="{{ route('home') }}">← Kembali ke halaman utama</a>
                <h1 class="mb-10 text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">Masuk</h1>

                <form class="space-y-6" action="{{ route('login.store') }}" method="POST">
                    @csrf
                    <div>
                        <label class="mb-3 block text-xl font-bold sm:text-2xl" for="identifier">Username/Email</label>
                        <input
                            @class([
                                'h-[55px] w-full rounded-card border bg-transparent px-6 text-base outline-none transition placeholder:text-brand-muted focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30',
                                'border-brand-danger' => $errors->has('identifier'),
                                'border-brand-lineart' => ! $errors->has('identifier'),
                            ])
                            id="identifier"
                            name="identifier"
                            type="text"
                            value="{{ old('identifier') }}"
                            autocomplete="username"
                            placeholder="Masukkan username/email disini"
                            required
                            @if ($errors->has('identifier'))
                                aria-invalid="true"
                                aria-describedby="identifier-error"
                            @endif
                        >
                        @error('identifier')
                            <p class="mt-2 text-sm font-medium text-brand-danger" id="identifier-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-3 block text-xl font-bold sm:text-2xl" for="password">Password</label>
                        <input
                            @class([
                                'h-[55px] w-full rounded-card border bg-transparent px-6 text-base outline-none transition placeholder:text-brand-muted focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30',
                                'border-brand-danger' => $errors->has('password'),
                                'border-brand-lineart' => ! $errors->has('password'),
                            ])
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Masukkan password disini"
                            minlength="8"
                            required
                            aria-describedby="password-hint{{ $errors->has('password') ? ' password-error' : '' }}"
                        >
                        <p class="mt-2 text-sm text-brand-muted" id="password-hint">Password minimal 8 karakter</p>
                        @error('password')
                            <p class="mt-2 text-sm font-medium text-brand-danger" id="password-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end sm:gap-6">
                        <button class="min-h-11 w-full rounded-card bg-brand-primary px-7 py-2 text-lg font-bold text-white transition hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink sm:w-auto sm:min-w-[8.6875rem]" type="submit">Masuk</button>
                        <a class="inline-flex min-h-11 w-full items-center justify-center rounded-card bg-brand-primary px-7 py-2 text-lg font-bold text-white transition hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink sm:w-auto sm:min-w-[8.4375rem]" href="{{ route('register') }}">Daftar</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
@endsection
