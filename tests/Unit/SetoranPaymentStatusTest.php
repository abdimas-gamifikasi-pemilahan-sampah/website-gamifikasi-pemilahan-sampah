<?php

namespace Tests\Unit;

use App\Models\Setoran;
use Tests\TestCase;

class SetoranPaymentStatusTest extends TestCase
{
    public function test_status_pembayaran_turunan_untuk_warga_belum_bayar(): void
    {
        $setoran = new Setoran([
            'nilai' => -12000,
            'status_pembayaran' => 'belum_dibayar',
        ]);

        $this->assertTrue($setoran->isPaymentByWarga());
        $this->assertSame('belum_dibayar_warga', $setoran->paymentStatusKey());
        $this->assertSame('Belum Dibayar Warga', $setoran->paymentStatusLabel());
    }

    public function test_status_pembayaran_turunan_untuk_petugas_sudah_bayar(): void
    {
        $setoran = new Setoran([
            'nilai' => 18500,
            'status_pembayaran' => 'sudah_dibayar',
        ]);

        $this->assertTrue($setoran->isPaymentByPetugas());
        $this->assertSame('sudah_dibayar_petugas', $setoran->paymentStatusKey());
        $this->assertSame('Sudah Dibayar Petugas', $setoran->paymentStatusLabel());
    }

    public function test_status_pembayaran_netral_tidak_menunjukkan_pihak_pembayar(): void
    {
        $setoran = new Setoran([
            'nilai' => 0,
            'status_pembayaran' => 'belum_dibayar',
        ]);

        $this->assertFalse($setoran->hasPaymentFlow());
        $this->assertSame('tidak_ada_pembayaran', $setoran->paymentStatusKey());
        $this->assertSame('Tidak Ada Pembayaran', $setoran->paymentStatusLabel());
    }
}
