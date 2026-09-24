<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Utils\StrictAssociative;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpPawapay\Enums\Country;
use AndyDefer\PhpPawapay\Enums\Language;
use AndyDefer\PhpPawapay\ValueObjects\CustomerMessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;

final class CreatePaymentPageRecord extends AbstractRecord
{
    public function __construct(
        public readonly UuidVO $depositId,
        public readonly UrlVO $returnUrl,
        public readonly AmountDetailsRecord $amountDetails,
        public readonly PhoneNumberVO $phoneNumber,
        public readonly Language $language,
        public readonly Country $country,
        public readonly ?CustomerMessageVO $customerMessage = null,
        public readonly ?MetadataVO $metadata = null,
        public readonly ?StrictAssociative $data = null,
    ) {}
}
