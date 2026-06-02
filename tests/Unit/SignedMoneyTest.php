<?php

namespace Tests\Unit;

use App\Support\SignedMoney;
use PHPUnit\Framework\TestCase;

class SignedMoneyTest extends TestCase
{
    public function test_it_formats_signed_values_consistently(): void
    {
        $this->assertSame('-Rp 15.000', SignedMoney::formatSigned(15000));
        $this->assertSame('+Rp 7.500', SignedMoney::formatSigned(-7500));
        $this->assertSame('Rp 0', SignedMoney::formatSigned(0));
    }

    public function test_it_builds_contextual_transaction_labels(): void
    {
        $this->assertSame('Pengeluaran Rp 12.000', SignedMoney::describe(12000));
        $this->assertSame('Pemasukan Rp 6.000', SignedMoney::describe(-6000));
        $this->assertSame('Tidak ada pemasukan atau pengeluaran', SignedMoney::describe(0));
    }
}
