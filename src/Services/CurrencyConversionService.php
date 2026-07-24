<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Services;

use AndyDefer\PhpPawapay\Contracts\CurrencyConversionInterface;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use RuntimeException;

final class CurrencyConversionService implements CurrencyConversionInterface
{
    /**
     * @var array<string, float>
     */
    private array $rates;

    private Currency $baseCurrency;

    /**
     * @param  array<string, float>  $rates  Tableau des taux [devise => taux]
     * @param  Currency  $baseCurrency  Devise de base (par défaut USD)
     */
    public function __construct(array $rates, Currency $baseCurrency = Currency::USD)
    {
        $this->rates = $rates;
        $this->baseCurrency = $baseCurrency;

        // Ajoute la devise de base si manquante
        if (! isset($this->rates[$this->baseCurrency->value])) {
            $this->rates[$this->baseCurrency->value] = 1.0;
        }
    }

    public function convert(AmountVO $amount, Currency $from, Currency $to, int $precision = 2): AmountVO
    {
        if ($from === $to) {
            return $amount;
        }

        $fromRate = $this->rates[$from->value] ?? null;
        $toRate = $this->rates[$to->value] ?? null;

        if ($fromRate === null || $toRate === null) {
            throw new RuntimeException(
                sprintf('Taux non disponible pour %s -> %s', $from->value, $to->value)
            );
        }

        // Conversion via la devise de base
        $amountInBase = $amount->divide($fromRate);
        $converted = $amountInBase->multiply($toRate);

        return $converted;
    }

    public function getRate(Currency $from, Currency $to): ?float
    {
        if ($from === $to) {
            return 1.0;
        }

        $fromRate = $this->rates[$from->value] ?? null;
        $toRate = $this->rates[$to->value] ?? null;

        if ($fromRate === null || $toRate === null) {
            return null;
        }

        return $toRate / $fromRate;
    }

    public function format(AmountVO $amount, Currency $currency): string
    {
        $symbol = $currency->getSymbol();
        $formatted = number_format($amount->toFloat(), 2, ',', ' ');

        if (in_array($currency, [Currency::CDF, Currency::XAF, Currency::XOF], true)) {
            return sprintf('%s %s', $formatted, $symbol);
        }

        return $symbol.$formatted;
    }
}
