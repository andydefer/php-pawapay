<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Contracts;

use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;

interface CurrencyConversionInterface
{
    /**
     * Convertit un montant d'une devise à une autre.
     *
     * @param  AmountVO  $amount  Le montant à convertir
     * @param  Currency  $from  La devise source
     * @param  Currency  $to  La devise cible
     * @param  int  $precision  Nombre de décimales (par défaut 2)
     * @return AmountVO Le montant converti
     *
     * @throws \RuntimeException Si les taux ne sont pas disponibles
     */
    public function convert(AmountVO $amount, Currency $from, Currency $to, int $precision = 2): AmountVO;

    /**
     * Obtient le taux de conversion entre deux devises.
     *
     * @param  Currency  $from  La devise source
     * @param  Currency  $to  La devise cible
     * @return float|null Le taux de conversion ou null si non disponible
     */
    public function getRate(Currency $from, Currency $to): ?float;

    /**
     * Formate un montant avec le symbole de la devise.
     *
     * @param  AmountVO  $amount  Le montant à formater
     * @param  Currency  $currency  La devise à utiliser
     * @return string Le montant formaté avec le symbole
     */
    public function format(AmountVO $amount, Currency $currency): string;
}
