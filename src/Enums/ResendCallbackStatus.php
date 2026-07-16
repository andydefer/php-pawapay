<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Enums;

enum ResendCallbackStatus: string
{
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';

    public function isAccepted(): bool
    {
        return $this === self::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }
}
