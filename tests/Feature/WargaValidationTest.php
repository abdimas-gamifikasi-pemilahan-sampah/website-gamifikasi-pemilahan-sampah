<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WargaValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_nomor_kk_harus_16_digit_angka_di_form_web(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('sips.warga.store'), [
            'nama' => 'Siti Aminah',
            'no_kk' => '1234abcd5678efgh',
            'rt' => 1,
            'rw' => 2,
            'dusun' => 'Banjarsari',
            'no_hp' => '08123456789',
            'tanggal_terdaftar' => now()->toDateString(),
            'status_keanggotaan' => 'aktif',
        ]);

        $response->assertSessionHasErrors([
            'no_kk' => 'Nomor KK harus 16 digit angka.',
        ]);
    }

    public function test_nomor_kk_valid_dapat_disimpan(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('sips.warga.store'), [
            'nama' => 'Siti Aminah',
            'no_kk' => '1234567890123456',
            'rt' => 1,
            'rw' => 2,
            'dusun' => 'Banjarsari',
            'no_hp' => '08123456789',
            'tanggal_terdaftar' => now()->toDateString(),
            'status_keanggotaan' => 'aktif',
        ]);

        $response->assertRedirect(route('sips.warga.index'));
        $this->assertDatabaseHas('warga', [
            'nama' => 'Siti Aminah',
            'no_kk' => '1234567890123456',
        ]);
    }
}
