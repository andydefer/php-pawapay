<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;

final class PredictProviderStruct extends Struct
{
    public function __construct(
        public readonly PhoneNumberVO $phoneNumber,
    ) {}
}
