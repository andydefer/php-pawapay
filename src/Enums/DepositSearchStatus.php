<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Enums;

enum DepositSearchStatus: string
{
    case FOUND = 'FOUND';
    case NOT_FOUND = 'NOT_FOUND';
    case REJECTED = 'REJECTED';

    public function isFound(): bool
    {
        return $this === self::FOUND;
    }

    public function isNotFound(): bool
    {
        return $this === self::NOT_FOUND;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }
}
