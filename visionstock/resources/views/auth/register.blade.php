@extends('layouts.guest', ['title' => 'Registrasi'])

@section('content')
    <main class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden min-h-screen overflow-hidden lg:block">
            <img class="absolute inset-0 size-full object-cover" src="{{ asset('images/image-login-regis.png') }}" alt="Rak persediaan di toko">
        </div>

        <section class="flex min-h-screen items-center justify-center px-6 py-12 sm:px-12 lg:px-16 xl:px-24">
            <div class="w-full max-w-xl">
                <a class="mb-8 inline-block text-sm font-semibold text-brand-ink/75 hover:text-brand-ink" href="{{ route('home') }}">← Kembali ke halaman utama</a>
                <h1 class="mb-10 text-5xl font-bold tracking-tight sm:text-6xl">Registrasi</h1>

                <form class="space-y-6" action="{{ route('register.store') }}" method="POST">
                    @csrf
                    <div>
                        <label class="mb-3 block text-xl font-semibold" for="identifier">Username/Email</label>
                        <input
                            @class([
                                'w-full rounded-2xl border bg-transparent px-5 py-4 text-base outline-none transition placeholder:text-brand-ink/45 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30',
                                'border-red-800' => $errors->has('identifier'),
                                'border-brand-ink/80' => ! $errors->has('identifier'),
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
                            <p class="mt-2 text-sm font-medium text-red-800" id="identifier-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-3 block text-xl font-semibold" for="password">Password</label>
                        <input
                            @class([
                                'w-full rounded-2xl border bg-transparent px-5 py-4 text-base outline-none transition placeholder:text-brand-ink/45 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30',
                                'border-red-800' => $errors->has('password'),
                                'border-brand-ink/80' => ! $errors->has('password'),
                            ])
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Masukkan password disini"
                            minlength="8"
                            required
                            aria-describedby="password-hint{{ $errors->has('password') ? ' password-error' : '' }}"
                        >
                        <p class="mt-2 text-sm text-brand-ink/65" id="password-hint">Password minimal 8 karakter</p>
                        @error('password')
                            <p class="mt-2 text-sm font-medium text-red-800" id="password-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-3 block text-xl font-semibold" for="password_confirmation">Ulangi Password</label>
                        <input
                            @class([
                                'w-full rounded-2xl border bg-transparent px-5 py-4 text-base outline-none transition placeholder:text-brand-ink/45 focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/30',
                                'border-red-800' => $errors->has('password'),
                                'border-brand-ink/80' => ! $errors->has('password'),
                            ])
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Masukkan ulang password"
                            required
                            @if ($errors->has('password'))
                                aria-invalid="true"
                                aria-describedby="password-confirmation-error"
                            @endif
                        >
                        @error('password')
                            <p class="mt-2 text-sm font-medium text-red-800" id="password-confirmation-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap justify-end gap-3 pt-1">
                        <button class="min-w-40 rounded-full bg-brand-primary px-7 py-3 text-lg font-bold text-white transition hover:brightness-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink" type="submit">Daftar</button>
                        <a class="min-w-40 rounded-full border-2 border-brand-primary px-7 py-3 text-center text-lg font-bold text-brand-ink transition hover:bg-white/30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink" href="{{ route('login') }}">Masuk</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
@endsection
