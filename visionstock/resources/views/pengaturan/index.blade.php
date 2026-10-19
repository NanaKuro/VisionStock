@extends('layouts.app', ['title' => 'Pengaturan'])

@section('content')
    <div class="mx-auto w-full max-w-[1200px]">
        @if (session('status'))
            <p class="mb-6 rounded-card bg-brand-primary px-5 py-4 text-base font-medium text-white" role="status">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-card border border-brand-danger bg-white/50 px-5 py-4 text-sm font-medium text-brand-danger" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($section === 'menu')
            <section aria-labelledby="settings-heading">
                <h1 id="settings-heading" class="mb-8 text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">Profil dan Akun</h1>
                <div class="flex w-full max-w-[511px] flex-col gap-4">
                    <a class="inline-flex min-h-12 items-center rounded-card bg-brand-primary px-6 text-lg font-bold text-white transition hover:brightness-95" href="{{ route('pengaturan.index', ['bagian' => 'profil']) }}">Edit Profil</a>
                    <a class="inline-flex min-h-12 items-center rounded-card bg-brand-primary px-6 text-lg font-bold text-white transition hover:brightness-95" href="{{ route('pengaturan.index', ['bagian' => 'whatsapp']) }}">Hubungkan ke WhatsApp</a>
                </div>
            </section>
        @elseif ($section === 'profil')
            <section class="max-w-[945px]" aria-labelledby="profile-heading">
                <h1 id="profile-heading" class="mb-8 text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">Edit Profil</h1>
                <form class="grid gap-6" action="{{ route('pengaturan.profil.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="mb-3 block text-lg font-bold" for="profile-name">Nama</label>
                        <input class="h-[55px] w-full rounded-card border border-brand-lineart bg-transparent px-6 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="profile-name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                        @error('name')
                            <p class="mt-2 text-sm text-brand-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-3 block text-lg font-bold" for="profile-email">Email</label>
                        <input class="h-[55px] w-full rounded-card border border-brand-lineart bg-transparent px-6 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                        @error('email')
                            <p class="mt-2 text-sm text-brand-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-3 block text-lg font-bold" for="profile-password">Atur Ulang Password</label>
                        <input class="h-[55px] w-full rounded-card border border-brand-lineart bg-transparent px-6 text-base outline-none placeholder:text-brand-muted focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="profile-password" name="password" type="password" autocomplete="new-password" placeholder="Kosongkan jika tidak ingin mengubah password">
                        @error('password')
                            <p class="mt-2 text-sm text-brand-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-3 block text-lg font-bold" for="profile-password-confirmation">Konfirmasi Password</label>
                        <input class="h-[55px] w-full rounded-card border border-brand-lineart bg-transparent px-6 text-base outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25" id="profile-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end sm:gap-6">
                        <a class="inline-flex min-h-11 items-center justify-center rounded-card border border-brand-lineart/40 px-7 font-bold transition hover:bg-white/35" href="{{ route('pengaturan.index') }}">Kembali</a>
                        <button class="min-h-11 rounded-card bg-brand-primary px-7 font-bold text-white transition hover:brightness-95" type="submit">Simpan</button>
                    </div>
                </form>
            </section>
        @else
            <section class="max-w-[945px]" aria-labelledby="whatsapp-heading">
                <h1 id="whatsapp-heading" class="mb-8 text-[clamp(1.75rem,3.4vw,3rem)] font-bold tracking-tight">Hubungkan ke WhatsApp</h1>
                @if ($user->whatsapp_number)
                    <p class="mb-6 text-base text-brand-ink">
                        Nomor tersimpan: <span class="font-bold">{{ $user->whatsapp_number }}</span>
                        <span class="block text-sm text-brand-muted">WhatsApp belum terhubung.</span>
                    </p>
                @endif
                <form class="grid gap-6" action="{{ route('pengaturan.whatsapp.save') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-3 block text-lg font-bold" for="whatsapp-number">Nomor Telepon</label>
                        <input
                            class="h-[55px] w-full rounded-card border border-brand-lineart bg-transparent px-6 text-base outline-none placeholder:text-brand-muted focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/25"
                            id="whatsapp-number"
                            name="whatsapp_number"
                            type="tel"
                            value="{{ old('whatsapp_number', $user->whatsapp_number) }}"
                            placeholder="+6281234567890"
                            autocomplete="tel"
                            pattern="\+[1-9][0-9]{7,14}"
                            required
                        >
                        <p class="mt-2 text-sm text-brand-muted">Gunakan format internasional, misalnya +6281234567890.</p>
                        @error('whatsapp_number')
                            <p class="mt-2 text-sm text-brand-danger" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end sm:gap-6">
                        <a class="inline-flex min-h-11 items-center justify-center rounded-card border border-brand-lineart/40 px-7 font-bold transition hover:bg-white/35" href="{{ route('pengaturan.index') }}">Kembali</a>
                        <button class="min-h-11 rounded-card bg-brand-primary px-7 font-bold text-white transition hover:brightness-95" type="submit">Kirim</button>
                    </div>
                </form>
            </section>
        @endif
    </div>
@endsection
