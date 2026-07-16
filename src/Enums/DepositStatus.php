<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Enums;

enum DepositStatus: string
{
    case ACCEPTED = 'ACCEPTED';
    case DUPLICATE_IGNORED = 'DUPLICATE_IGNORED';
    case REJECTED = 'REJECTED';

    public function isAccepted(): bool
    {
        return $this === self::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function isDuplicateIgnored(): bool
    {
        return $this === self::DUPLICATE_IGNORED;
    }
}
