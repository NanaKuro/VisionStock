<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt([
            'login_identifier' => $credentials['identifier'],
            'password' => $credentials['password'],
        ])) {
            return back()
                ->withErrors([
                    'identifier' => 'Username/email atau password yang dimasukkan salah.',
                ])
                ->onlyInput('identifier');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegistration(): View
    {
        return view('auth.register');
    }

    public function register(RegistrationRequest $request): RedirectResponse
    {
        $identifier = trim($request->validated('identifier'));
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        $user = User::create([
            'name' => $identifier,
            'email' => $isEmail ? $identifier : null,
            'username' => $isEmail ? null : $identifier,
            'login_identifier' => $identifier,
            'password' => $request->validated('password'),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
