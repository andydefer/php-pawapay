<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use InvalidArgumentException;

final class AmountVO extends AbstractValueObject
{
    private const DECIMALS = 2;

    private readonly string $value;

    public function __construct(float $value)
    {
        if (! is_finite($value)) {
            throw new InvalidArgumentException('Invalid amount: value must be a finite number');
        }

        if ($value <= 0) {
            throw new InvalidArgumentException(sprintf('Amount must be positive: %s', (string) $value));
        }

        $this->value = $this->formatNumber((string) $value);
    }

    /**
     * Get the amount without trailing zero decimals when they are "00".
     *
     * The value is stored internally with two decimals for precision, but
     * when the decimal part is exactly "00" it is not meaningful for display
     * or transport (e.g. "40000.00" is returned as "40000").
     */
    public function getValue(): string
    {
        return $this->stripEmptyDecimals($this->value);
    }

    /**
     * Get the amount with the full two-decimal precision.
     */
    public function toString(): string
    {
        return $this->value;
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    public function toInt(): int
    {
        return (int) round($this->toFloat() * 100);
    }

    public function add(self $other): self
    {
        $result = bcadd($this->value, $other->value, self::DECIMALS);

        return new self((float) $result);
    }

    public function subtract(self $other): self
    {
        $result = bcsub($this->value, $other->value, self::DECIMALS);

        return new self((float) $result);
    }

    public function multiply(float|int|string $multiplier): self
    {
        $result = bcmul($this->value, (string) $multiplier, self::DECIMALS);

        return new self((float) $result);
    }

    public function divide(float|int|string $divisor): self
    {
        if ((float) $divisor === 0.0) {
            throw new InvalidArgumentException('Division by zero');
        }
        $result = bcdiv($this->value, (string) $divisor, self::DECIMALS);

        return new self((float) $result);
    }

    public function percentage(float|int|string $percent): self
    {
        $factor = bcdiv((string) $percent, '100', self::DECIMALS + 2);

        return $this->multiply($factor);
    }

    public function isZero(): bool
    {
        return bccomp($this->value, '0', self::DECIMALS) === 0;
    }

    public function isPositive(): bool
    {
        return bccomp($this->value, '0', self::DECIMALS) > 0;
    }

    public function isNegative(): bool
    {
        return bccomp($this->value, '0', self::DECIMALS) < 0;
    }

    public function toNormal(int $zeros = 1): self
    {
        if ($zeros < 0) {
            throw new InvalidArgumentException('Number of trailing zeros must be greater than or equal to 0');
        }

        $divisor = bcpow('10', (string) $zeros, 0);
        $floor = bcmul(
            bcdiv($this->value, $divisor, 0),
            $divisor,
            self::DECIMALS,
        );

        if (bccomp($floor, $this->value, self::DECIMALS) === 0) {
            return new self((float) $this->value);
        }

        $ceiled = bcadd($floor, $divisor, self::DECIMALS);

        return new self((float) $ceiled);
    }

    private function formatNumber(string $value): string
    {
        $sign = '';
        if (str_starts_with($value, '-')) {
            $sign = '-';
            $value = substr($value, 1);
        }

        $parts = explode('.', $value);
        $integer = ltrim($parts[0], '0') ?: '0';

        if (isset($parts[1])) {
            $decimal = substr($parts[1], 0, 2);
            $decimal = str_pad($decimal, 2, '0');
        } else {
            $decimal = '00';
        }

        return $sign.$integer.'.'.$decimal;
    }

    /**
     * Remove the decimal part when it is exactly "00".
     *
     * Only "00" is stripped — values like "40.10" or "40.50" keep their
     * decimals because they carry meaningful information.
     */
    private function stripEmptyDecimals(string $value): string
    {
        if (str_ends_with($value, '.00')) {
            return substr($value, 0, -3);
        }

        return $value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
