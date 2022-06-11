<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\CurrencyService;

class CurrencyTest extends TestCase
{
    public function test_konversi_usd_ke_idr()
    {
        $amount_in_usd = 99;

        // cek apakah hasil convert sama dengan hasil yang diharapkan
        $this->assertEquals(14571 * $amount_in_usd, (new CurrencyService())->convert($amount_in_usd, 'usd', 'idr'));
    }

    public function test_konversi_usd_ke_eur()
    {
        $amount_in_usd = 99;

        // cek apakah hasil convert sama dengan hasil yang diharapkan
        $this->assertEquals(0.95 * $amount_in_usd, (new CurrencyService())->convert($amount_in_usd, 'usd', 'eur'));
    }
}
