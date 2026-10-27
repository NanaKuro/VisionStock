<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_displays_profile_and_analysis_history_sections(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pengaturan.index'))
            ->assertOk()
            ->assertSee('Profil dan Akun')
            ->assertSee('Edit Profil')
            ->assertSee('Riwayat Analisis');

        $this->get(route('pengaturan.index', ['bagian' => 'profil']))
            ->assertOk()
            ->assertSee('Atur Ulang Password')
            ->assertSee('Konfirmasi Password');

        $this->get(route('pengaturan.riwayat-analisis.index'))
            ->assertOk()
            ->assertSee('Riwayat analisis belum ada')
            ->assertSee('Simpan hasil analisis dari halaman Analisis Stok.');
    }

    public function test_user_can_update_profile_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@example.com',
            'login_identifier' => 'lama@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->put(route('pengaturan.profil.update'), [
                'name' => 'Pemilik Baru',
                'email' => 'baru@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ])
            ->assertRedirect(route('pengaturan.index', ['bagian' => 'profil']))
            ->assertSessionHas('status', 'Profil berhasil diperbarui.');

        $user->refresh();

        $this->assertSame('Pemilik Baru', $user->name);
        $this->assertSame('baru@example.com', $user->email);
        $this->assertSame('baru@example.com', $user->login_identifier);
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_username_login_identifier_is_preserved_when_profile_email_is_added(): void
    {
        $user = User::factory()->create([
            'username' => 'warung_maju',
            'email' => null,
            'login_identifier' => 'warung_maju',
        ]);

        $this->actingAs($user)
            ->put(route('pengaturan.profil.update'), [
                'name' => 'Warung Maju',
                'email' => 'pemilik@example.com',
            ])
            ->assertRedirect(route('pengaturan.index', ['bagian' => 'profil']));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'pemilik@example.com',
            'login_identifier' => 'warung_maju',
        ]);
    }

    public function test_profile_rejects_duplicate_email_and_mismatched_password_confirmation(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('pengaturan.index', ['bagian' => 'profil']))
            ->put(route('pengaturan.profil.update'), [
                'name' => 'Pemilik',
                'email' => 'taken@example.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'different123',
            ])
            ->assertRedirect(route('pengaturan.index', ['bagian' => 'profil']))
            ->assertSessionHasErrors(['email', 'password']);
    }
}
