<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\ValueObjects;

use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CustomerMessageVOTest extends TestCase
{
    public function test_create_with_valid_message(): void
    {
        $vo = new CustomerMessageVO('Paiement commande');
        $this->assertSame('paiement commande', $vo->getValue());
        $this->assertSame('paiement commande', $vo->toString());
    }

    public function test_normalizes_case(): void
    {
        $vo = new CustomerMessageVO('PAIEMENT COMMANDE');
        $this->assertSame('paiement commande', $vo->getValue());
    }

    public function test_normalizes_diacritics(): void
    {
        $vo = new CustomerMessageVO('Paiement réussi');
        $this->assertSame('paiement reussi', $vo->getValue());
    }

    public function test_removes_emoji(): void
    {
        $vo = new CustomerMessageVO('Paiement 🎉');
        $this->assertSame('paiement', $vo->getValue());
    }

    public function test_removes_currency_symbols(): void
    {
        $vo = new CustomerMessageVO('100$ valide');
        $this->assertSame('100 dollar valide', $vo->getValue());
    }

    public function test_removes_special_chars(): void
    {
        $vo = new CustomerMessageVO('Paiement#commande!');
        $this->assertSame('paiement commande', $vo->getValue());
    }

    public function test_collapses_spaces(): void
    {
        $vo = new CustomerMessageVO('Paiement    commande');
        $this->assertSame('paiement commande', $vo->getValue());
    }

    public function test_min_length_exactly_four(): void
    {
        $vo = new CustomerMessageVO('test');
        $this->assertSame('test', $vo->getValue());
    }

    public function test_max_length_exactly_twenty_two(): void
    {
        $vo = new CustomerMessageVO('abcdefghijklmnopqrstuv');
        $this->assertSame('abcdefghijklmnopqrstuv', $vo->getValue());
        $this->assertSame(22, mb_strlen($vo->getValue()));
    }

    public function test_too_short_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer message must be at least 4 characters, 3 given.');
        new CustomerMessageVO('abc');
    }

    public function test_empty_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer message must be at least 4 characters, 0 given.');
        new CustomerMessageVO('');
    }

    public function test_whitespace_only_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer message must be at least 4 characters, 0 given.');
        new CustomerMessageVO('    ');
    }

    public function test_too_long_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer message must not exceed 22 characters, 23 given.');
        new CustomerMessageVO('abcdefghijklmnopqrstuvw');
    }

    public function test_too_long_after_normalization_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer message must not exceed 22 characters');
        new CustomerMessageVO('paiement pour la commande');
    }

    public function test_get_value_returns_string(): void
    {
        $vo = new CustomerMessageVO('paiement commande');
        $this->assertIsString($vo->getValue());
    }

    public function test_to_string_returns_string(): void
    {
        $vo = new CustomerMessageVO('paiement commande');
        $this->assertIsString($vo->toString());
    }

    public function test_to_string_magic_method(): void
    {
        $vo = new CustomerMessageVO('paiement commande');
        $this->assertSame('paiement commande', (string) $vo);
    }

    public function test_immutability(): void
    {
        $vo = new CustomerMessageVO('paiement commande');
        $value1 = $vo->getValue();
        $value2 = $vo->getValue();
        $this->assertSame($value1, $value2);
    }
}
