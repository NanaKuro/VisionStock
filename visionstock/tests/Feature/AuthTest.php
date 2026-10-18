<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_offers_login_and_registration(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Solusi')
            ->assertSee('Permasalahan')
            ->assertSee(route('login'))
            ->assertSee(route('register'));
    }

    public function test_user_can_register_with_a_username_and_is_logged_in(): void
    {
        $response = $this->post(route('register.store'), [
            'identifier' => 'warung_maju',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'warung_maju',
            'username' => 'warung_maju',
            'email' => null,
            'login_identifier' => 'warung_maju',
        ]);

        $user = User::where('login_identifier', 'warung_maju')->firstOrFail();
        $this->assertNotSame('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_can_register_with_an_email_address(): void
    {
        $this->post(route('register.store'), [
            'identifier' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'username' => null,
            'login_identifier' => 'admin@example.com',
        ]);
    }

    public function test_registration_rejects_short_or_mismatched_passwords(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'identifier' => 'pemilik',
                'password' => 'pendek',
                'password_confirmation' => 'berbeda',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors([
                'password' => 'Password minimal 8 karakter.',
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_an_existing_identifier(): void
    {
        User::factory()->create([
            'login_identifier' => 'warung_maju',
            'username' => 'warung_maju',
            'email' => null,
        ]);

        $this->post(route('register.store'), [
            'identifier' => 'warung_maju',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors([
            'identifier' => 'Username/email tersebut sudah terdaftar.',
        ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_user_can_log_in_with_their_identifier(): void
    {
        $user = User::factory()->create([
            'login_identifier' => 'pemilik_toko',
            'username' => 'pemilik_toko',
            'email' => null,
        ]);

        $this->post(route('login.store'), [
            'identifier' => 'pemilik_toko',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_log_in_with_an_email_address(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'identifier' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_failed_login_returns_an_indonesian_error(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'identifier' => 'tidak_terdaftar',
                'password' => 'password123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'identifier' => 'Username/email atau password yang dimasukkan salah.',
            ]);

        $this->assertGuest();
    }

    public function test_protected_pages_redirect_guests_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
