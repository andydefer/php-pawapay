<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\ValueObjects;

use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AmountVOTest extends TestCase
{
    public function test_create_amount_from_float(): void
    {
        $amount = new AmountVO(99.99);
        $this->assertSame('99.99', $amount->getValue());
        $this->assertSame('99.99', $amount->toString());
        $this->assertSame(99.99, $amount->toFloat());
    }

    public function test_create_amount_from_integer(): void
    {
        $amount = new AmountVO(100.0);
        $this->assertSame('100.00', $amount->getValue());
        $this->assertSame('100.00', $amount->toString());
    }

    public function test_create_amount_from_float_with_more_decimals_rounds(): void
    {
        $amount = new AmountVO(99.999);
        $this->assertSame('99.99', $amount->getValue());
        $this->assertSame('99.99', $amount->toString());
    }

    public function test_create_amount_from_float_with_2_decimals(): void
    {
        $amount = new AmountVO(99.99);
        $this->assertSame('99.99', $amount->getValue());
        $this->assertSame('99.99', $amount->toString());
    }

    public function test_create_amount_from_float_with_1_decimal(): void
    {
        $amount = new AmountVO(99.9);
        $this->assertSame('99.90', $amount->getValue());
        $this->assertSame('99.90', $amount->toString());
    }

    public function test_create_amount_from_float_with_no_decimals(): void
    {
        $amount = new AmountVO(100.0);
        $this->assertSame('100.00', $amount->getValue());
        $this->assertSame('100.00', $amount->toString());
    }

    public function test_create_amount_from_float_with_large_value(): void
    {
        $amount = new AmountVO(9999999.99);
        $this->assertSame('9999999.99', $amount->getValue());
        $this->assertSame('9999999.99', $amount->toString());
    }

    public function test_create_amount_from_float_with_small_value(): void
    {
        $amount = new AmountVO(0.01);
        $this->assertSame('0.01', $amount->getValue());
        $this->assertSame('0.01', $amount->toString());
    }

    public function test_create_amount_with_zero_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be positive: 0');
        new AmountVO(0.00);
    }

    public function test_create_amount_with_negative_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be positive: -10');
        new AmountVO(-10.00);
    }

    public function test_create_amount_with_nan_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid amount');
        new AmountVO(NAN);
    }

    public function test_create_amount_with_infinite_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid amount');
        new AmountVO(INF);
    }

    public function test_get_value_returns_string(): void
    {
        $amount = new AmountVO(99.99);
        $this->assertIsString($amount->getValue());
        $this->assertSame('99.99', $amount->getValue());
    }

    public function test_to_string_returns_string(): void
    {
        $amount = new AmountVO(99.99);
        $this->assertIsString($amount->toString());
        $this->assertSame('99.99', $amount->toString());
    }

    public function test_to_float_returns_float(): void
    {
        $amount = new AmountVO(99.99);
        $this->assertIsFloat($amount->toFloat());
        $this->assertSame(99.99, $amount->toFloat());
    }

    public function test_to_int_returns_cents(): void
    {
        $amount = new AmountVO(99.99);
        $this->assertIsInt($amount->toInt());
        $this->assertSame(9999, $amount->toInt());
    }

    public function test_to_int_with_one_decimal(): void
    {
        $amount = new AmountVO(99.9);
        $this->assertSame(9990, $amount->toInt());
    }

    public function test_to_int_with_no_decimal(): void
    {
        $amount = new AmountVO(100.0);
        $this->assertSame(10000, $amount->toInt());
    }

    public function test_add(): void
    {
        $amount1 = new AmountVO(50.50);
        $amount2 = new AmountVO(25.25);
        $result = $amount1->add($amount2);
        $this->assertSame('75.75', $result->toString());
        $this->assertSame('75.75', $result->getValue());
    }

    public function test_add_with_float_rounding(): void
    {
        $amount1 = new AmountVO(50.505);
        $amount2 = new AmountVO(25.255);
        $result = $amount1->add($amount2);
        $this->assertSame('75.75', $result->toString());
        $this->assertSame('75.75', $result->getValue());
    }

    public function test_subtract(): void
    {
        $amount1 = new AmountVO(100.00);
        $amount2 = new AmountVO(30.30);
        $result = $amount1->subtract($amount2);
        $this->assertSame('69.70', $result->toString());
        $this->assertSame('69.70', $result->getValue());
    }

    public function test_subtract_with_rounding(): void
    {
        $amount1 = new AmountVO(100.005);
        $amount2 = new AmountVO(30.305);
        $result = $amount1->subtract($amount2);
        $this->assertSame('69.70', $result->toString());
        $this->assertSame('69.70', $result->getValue());
    }

    public function test_multiply(): void
    {
        $amount = new AmountVO(10.50);
        $result = $amount->multiply(2.5);
        $this->assertSame('26.25', $result->toString());
        $this->assertSame('26.25', $result->getValue());
    }

    public function test_multiply_with_string(): void
    {
        $amount = new AmountVO(10.50);
        $result = $amount->multiply('2.5');
        $this->assertSame('26.25', $result->toString());
        $this->assertSame('26.25', $result->getValue());
    }

    public function test_multiply_with_integer(): void
    {
        $amount = new AmountVO(10.50);
        $result = $amount->multiply(3);
        $this->assertSame('31.50', $result->toString());
        $this->assertSame('31.50', $result->getValue());
    }

    public function test_multiply_with_rounding(): void
    {
        $amount = new AmountVO(10.50);
        $result = $amount->multiply(2.5);
        $this->assertSame('26.25', $result->toString());
        $this->assertSame('26.25', $result->getValue());
    }

    public function test_divide(): void
    {
        $amount = new AmountVO(100.00);
        $result = $amount->divide(4);
        $this->assertSame('25.00', $result->toString());
        $this->assertSame('25.00', $result->getValue());
    }

    public function test_divide_with_float(): void
    {
        $amount = new AmountVO(100.00);
        $result = $amount->divide(2.5);
        $this->assertSame('40.00', $result->toString());
        $this->assertSame('40.00', $result->getValue());
    }

    public function test_divide_with_string(): void
    {
        $amount = new AmountVO(100.00);
        $result = $amount->divide('4');
        $this->assertSame('25.00', $result->toString());
        $this->assertSame('25.00', $result->getValue());
    }

    public function test_divide_with_rounding(): void
    {
        $amount = new AmountVO(100.00);
        $result = $amount->divide(3);
        $this->assertSame('33.33', $result->toString());
        $this->assertSame('33.33', $result->getValue());
    }

    public function test_divide_by_zero_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero');
        $amount = new AmountVO(100.00);
        $amount->divide(0);
    }

    public function test_divide_by_zero_float_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero');
        $amount = new AmountVO(100.00);
        $amount->divide(0.0);
    }

    public function test_percentage(): void
    {
        $amount = new AmountVO(200.00);
        $result = $amount->percentage(15);
        $this->assertSame('30.00', $result->toString());
        $this->assertSame('30.00', $result->getValue());
    }

    public function test_percentage_with_float(): void
    {
        $amount = new AmountVO(200.00);
        $result = $amount->percentage(15.5);
        $this->assertSame('31.00', $result->toString());
        $this->assertSame('31.00', $result->getValue());
    }

    public function test_percentage_with_string(): void
    {
        $amount = new AmountVO(200.00);
        $result = $amount->percentage('15');
        $this->assertSame('30.00', $result->toString());
        $this->assertSame('30.00', $result->getValue());
    }

    public function test_percentage_with_rounding(): void
    {
        $amount = new AmountVO(200.00);
        $result = $amount->percentage(15);
        $this->assertSame('30.00', $result->toString());
        $this->assertSame('30.00', $result->getValue());
    }

    public function test_is_zero_should_always_be_false(): void
    {
        $small = new AmountVO(0.01);
        $normal = new AmountVO(10.00);
        $large = new AmountVO(1000.00);
        $this->assertFalse($small->isZero());
        $this->assertFalse($normal->isZero());
        $this->assertFalse($large->isZero());
    }

    public function test_is_positive_always_true(): void
    {
        $small = new AmountVO(0.01);
        $normal = new AmountVO(10.00);
        $large = new AmountVO(1000.00);
        $this->assertTrue($small->isPositive());
        $this->assertTrue($normal->isPositive());
        $this->assertTrue($large->isPositive());
    }

    public function test_is_negative_always_false(): void
    {
        $small = new AmountVO(0.01);
        $normal = new AmountVO(10.00);
        $large = new AmountVO(1000.00);
        $this->assertFalse($small->isNegative());
        $this->assertFalse($normal->isNegative());
        $this->assertFalse($large->isNegative());
    }

    public function test_immutability(): void
    {
        $original = new AmountVO(100.00);
        $new = $original->add(new AmountVO(50.00));
        $this->assertNotSame($original, $new);
        $this->assertSame('100.00', $original->toString());
        $this->assertSame('150.00', $new->toString());
    }

    public function test_immutability_on_multiply(): void
    {
        $original = new AmountVO(100.00);
        $new = $original->multiply(2);
        $this->assertNotSame($original, $new);
        $this->assertSame('100.00', $original->toString());
        $this->assertSame('200.00', $new->toString());
    }

    public function test_immutability_on_divide(): void
    {
        $original = new AmountVO(100.00);
        $new = $original->divide(2);
        $this->assertNotSame($original, $new);
        $this->assertSame('100.00', $original->toString());
        $this->assertSame('50.00', $new->toString());
    }

    public function test_chaining_operations(): void
    {
        $result = (new AmountVO(100.00))
            ->add(new AmountVO(50.00))
            ->subtract(new AmountVO(30.00))
            ->multiply(2)
            ->percentage(10);
        $this->assertSame('24.00', $result->toString());
        $this->assertSame('24.00', $result->getValue());
    }

    public function test_chaining_operations_with_rounding(): void
    {
        $result = (new AmountVO(100.00))
            ->add(new AmountVO(50.00))
            ->subtract(new AmountVO(30.00))
            ->multiply(2)
            ->percentage(15);
        $this->assertSame('36.00', $result->toString());
        $this->assertSame('36.00', $result->getValue());
    }

    public function test_to_string_magic_method(): void
    {
        $amount = new AmountVO(99.99);
        $this->assertSame('99.99', (string) $amount);
    }

    public function test_to_string_magic_method_with_one_decimal(): void
    {
        $amount = new AmountVO(99.9);
        $this->assertSame('99.90', (string) $amount);
    }

    public function test_large_number_of_operations(): void
    {
        $amount = new AmountVO(1000.00);
        for ($i = 0; $i < 100; $i++) {
            $amount = $amount->add(new AmountVO(0.01));
        }
        $this->assertSame('1001.00', $amount->toString());
        $this->assertSame('1001.00', $amount->getValue());
    }

    public function test_precision_with_bcmath(): void
    {
        $amount = new AmountVO(0.1);
        $result = $amount->multiply(10);
        $this->assertSame('1.00', $result->toString());
        $this->assertSame('1.00', $result->getValue());
    }
}
