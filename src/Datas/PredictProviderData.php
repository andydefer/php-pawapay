<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

final class PredictProviderData extends AbstractData
{
    public function __construct(
        public readonly ?Country $country,
        public readonly ?Provider $provider,
        public readonly ?PhoneNumberVO $phoneNumber,
        public readonly bool $isFound,
        public readonly ?FailureReasonData $failureReason,
        public readonly bool $hasFailureReason,
    ) {}
}
