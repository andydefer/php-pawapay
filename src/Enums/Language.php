<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Enums;

enum Language: string
{
    case EN = 'EN';
    case FR = 'FR';

    public static function default(): self
    {
        return self::EN;
    }
}
