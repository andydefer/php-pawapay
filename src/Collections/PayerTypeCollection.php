<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpPawapay\Enums\PayerType;

/**
 * Collection of PayerType enums.
 *
 * @extends AbstractTypedCollection<PayerType>
 */
final class PayerTypeCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(PayerType::class);
    }

    public static function default(): self
    {
        $collection = new self;

        foreach (PayerType::cases() as $payerType) {
            $collection->add($payerType);
        }

        return $collection;
    }

    /**
     * @return array<int, string>
     */
    public function toValues(): array
    {
        return $this->map(fn (PayerType $payerType): string => $payerType->value)->toArray();
    }
}
