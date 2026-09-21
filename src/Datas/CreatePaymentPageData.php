<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\PhpClient\ValueObjects\UrlVO;

final class CreatePaymentPageData extends AbstractData
{
    public function __construct(
        public readonly ?UrlVO $redirectUrl,
        public readonly ?FailureReasonData $failureReason,
        public readonly bool $hasFailureReason,
    ) {}
}
