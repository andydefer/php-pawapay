<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\Services;

use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Services\CurrencyConversionService;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CurrencyConversionServiceTest extends TestCase
{
    private CurrencyConversionService $converter;

    private array $rates;

    protected function setUp(): void
    {
        $this->rates = [
            'USD' => 1.0,
            'CDF' => 2312.2,
            'XAF' => 575.26,
            'XOF' => 569.16,
            'ETB' => 60.0,
            'GHS' => 15.0,
            'KES' => 150.0,
            'LSL' => 19.0,
            'MWK' => 1700.0,
            'MZN' => 63.0,
            'NGN' => 1500.0,
            'RWF' => 1300.0,
            'SLE' => 22.0,
            'TZS' => 2500.0,
            'UGX' => 3800.0,
            'ZMW' => 25.0,
        ];

        $this->converter = new CurrencyConversionService($this->rates, Currency::USD);
    }

    public function test_convert_same_currency(): void
    {
        $amount = new AmountVO(100.00);
        $result = $this->converter->convert($amount, Currency::USD, Currency::USD);

        $this->assertEquals(100.00, $result->toFloat());
        $this->assertSame($amount, $result);
    }

    public function test_convert_usd_to_cdf(): void
    {
        $amount = new AmountVO(100.00);
        $result = $this->converter->convert($amount, Currency::USD, Currency::CDF);

        $this->assertEquals(231220.00, $result->toFloat());
    }

    public function test_convert_cdf_to_usd(): void
    {
        $amount = new AmountVO(2312.20);
        $result = $this->converter->convert($amount, Currency::CDF, Currency::USD);

        $this->assertEquals(1.00, $result->toFloat());
    }

    public function test_convert_xof_to_xaf(): void
    {
        $amount = new AmountVO(569.16);
        $result = $this->converter->convert($amount, Currency::XOF, Currency::XAF);

        $this->assertEquals(575.26, $result->toFloat());
    }

    public function test_convert_usd_to_ngn(): void
    {
        $amount = new AmountVO(100.00);
        $result = $this->converter->convert($amount, Currency::USD, Currency::NGN);

        $this->assertEquals(150000.00, $result->toFloat());
    }

    public function test_convert_usd_to_xof(): void
    {
        $amount = new AmountVO(100.00);
        $result = $this->converter->convert($amount, Currency::USD, Currency::XOF);

        $this->assertEquals(56916.00, $result->toFloat());
    }

    public function test_convert_xof_to_usd(): void
    {
        $amount = new AmountVO(569.16);
        $result = $this->converter->convert($amount, Currency::XOF, Currency::USD);

        $this->assertEquals(1.00, $result->toFloat());
    }

    public function test_convert_usd_to_xaf(): void
    {
        $amount = new AmountVO(100.00);
        $result = $this->converter->convert($amount, Currency::USD, Currency::XAF);

        $this->assertEquals(57526.00, $result->toFloat());
    }

    public function test_convert_xaf_to_usd(): void
    {
        $amount = new AmountVO(575.26);
        $result = $this->converter->convert($amount, Currency::XAF, Currency::USD);

        $this->assertEquals(1.00, $result->toFloat());
    }

    public function test_convert_with_missing_rate(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Taux non disponible pour USD -> XOF');

        $rates = [
            'USD' => 1.0,
            'CDF' => 2312.2,
        ];
        $converter = new CurrencyConversionService($rates, Currency::USD);
        $amount = new AmountVO(100.00);
        $converter->convert($amount, Currency::USD, Currency::XOF);
    }

    public function test_get_rate(): void
    {
        $rate = $this->converter->getRate(Currency::USD, Currency::CDF);
        $this->assertEquals(2312.2, $rate);

        $rate = $this->converter->getRate(Currency::CDF, Currency::USD);
        $this->assertEquals(1 / 2312.2, $rate);

        $rate = $this->converter->getRate(Currency::XOF, Currency::XAF);
        $this->assertEquals(575.26 / 569.16, $rate);
    }

    public function test_get_rate_same_currency(): void
    {
        $rate = $this->converter->getRate(Currency::USD, Currency::USD);
        $this->assertEquals(1.0, $rate);
    }

    public function test_get_rate_missing_currency(): void
    {
        $rates = [
            'USD' => 1.0,
            'CDF' => 2312.2,
        ];
        $converter = new CurrencyConversionService($rates, Currency::USD);

        $rate = $converter->getRate(Currency::USD, Currency::XOF);
        $this->assertNull($rate);
    }

    public function test_format_usd(): void
    {
        $amount = new AmountVO(1234.56);
        $formatted = $this->converter->format($amount, Currency::USD);
        $this->assertEquals('$1 234,56', $formatted);
    }

    public function test_format_cdf(): void
    {
        $amount = new AmountVO(1234.56);
        $formatted = $this->converter->format($amount, Currency::CDF);
        $this->assertEquals('1 234,56 FC', $formatted);
    }

    public function test_format_xof(): void
    {
        $amount = new AmountVO(1234.56);
        $formatted = $this->converter->format($amount, Currency::XOF);
        $this->assertEquals('1 234,56 CFA', $formatted);
    }

    public function test_format_xaf(): void
    {
        $amount = new AmountVO(1234.56);
        $formatted = $this->converter->format($amount, Currency::XAF);
        $this->assertEquals('1 234,56 CFA', $formatted);
    }

    public function test_format_ngn(): void
    {
        $amount = new AmountVO(1234.56);
        $formatted = $this->converter->format($amount, Currency::NGN);
        $this->assertEquals('₦1 234,56', $formatted);
    }

    public function test_constructor_with_missing_base_currency(): void
    {
        $rates = [
            'CDF' => 2312.2,
            'XOF' => 569.16,
        ];

        $converter = new CurrencyConversionService($rates, Currency::USD);

        $this->assertEquals(1.0, $converter->getRate(Currency::USD, Currency::USD));

        $amount = new AmountVO(100.00);
        $result = $converter->convert($amount, Currency::USD, Currency::CDF);
        $this->assertEquals(231220.00, $result->toFloat());
    }

    public function test_convert_with_large_amounts(): void
    {
        $amount = new AmountVO(999999.99);
        $result = $this->converter->convert($amount, Currency::USD, Currency::CDF);

        $expected = 999999.99 * 2312.2;
        $this->assertEqualsWithDelta($expected, $result->toFloat(), 0.01);
    }

    public function test_convert_with_small_amount(): void
    {
        $amount = new AmountVO(0.01);
        $result = $this->converter->convert($amount, Currency::USD, Currency::CDF);

        $this->assertEquals(23.12, $result->toFloat());
    }

    public function test_chained_conversions(): void
    {
        $amount = new AmountVO(100.00);

        $xof = $this->converter->convert($amount, Currency::USD, Currency::XOF);
        $this->assertEquals(56916.00, $xof->toFloat());

        $xaf = $this->converter->convert($xof, Currency::XOF, Currency::XAF);
        $this->assertEquals(57526.00, $xaf->toFloat());

        $xafDirect = $this->converter->convert($amount, Currency::USD, Currency::XAF);
        $this->assertEquals(57526.00, $xafDirect->toFloat());

        $this->assertEqualsWithDelta($xafDirect->toFloat(), $xaf->toFloat(), 0.01);
    }

    public function test_chained_conversions_usd_to_ngn_via_cdf(): void
    {
        $amount = new AmountVO(100.00);

        $cdf = $this->converter->convert($amount, Currency::USD, Currency::CDF);
        $this->assertEquals(231220.00, $cdf->toFloat());

        $ngn = $this->converter->convert($cdf, Currency::CDF, Currency::NGN);
        $this->assertEquals(150000.00, $ngn->toFloat());

        $ngnDirect = $this->converter->convert($amount, Currency::USD, Currency::NGN);
        $this->assertEquals(150000.00, $ngnDirect->toFloat());

        $this->assertEqualsWithDelta($ngnDirect->toFloat(), $ngn->toFloat(), 0.01);
    }
}
