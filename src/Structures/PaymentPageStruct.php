<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Language;
use AndyDefer\PhpPawapay\Graphs\AmountDetailsGraph;
use AndyDefer\PhpPawapay\ValueObjects\MessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

final class PaymentPageStruct extends Struct
{
    public function __construct(
        public readonly UuidVO $depositId,
        public readonly UrlVO $returnUrl,
        public readonly AmountDetailsGraph $amountDetails,
        public readonly PhoneNumberVO $phoneNumber,
        public readonly Language $language,
        public readonly Country $country,
        public readonly ?MessageVO $customerMessage = null,
        public readonly ?MessageVO $reason = null,
        public readonly ?MetadataVO $metadata = null,
    ) {}
}
