<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $section = $request->validate([
            'bagian' => ['nullable', Rule::in(['profil'])],
        ])['bagian'] ?? 'menu';

        return view('pengaturan.index', [
            'title' => 'Pengaturan',
            'section' => $section,
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah digunakan akun lain.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password belum sesuai.',
        ]);

        $previousEmail = $user->email;
        $user->name = $data['name'];
        $user->email = $data['email'];

        if ($previousEmail !== null && $user->login_identifier === $previousEmail) {
            $user->login_identifier = $data['email'];
        }

        if (($data['password'] ?? '') !== '') {
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()
            ->route('pengaturan.index', ['bagian' => 'profil'])
            ->with('status', 'Profil berhasil diperbarui.');
    }
}
