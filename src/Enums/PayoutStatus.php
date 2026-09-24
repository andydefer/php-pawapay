<?php

namespace AndyDefer\PhpPawapay\Enums;

enum PayoutStatus: string
{
    case ACCEPTED = 'ACCEPTED';
    case ENQUEUED = 'ENQUEUED';
    case PROCESSING = 'PROCESSING';
    case IN_RECONCILIATION = 'IN_RECONCILIATION';
    case COMPLETED = 'COMPLETED'; // final
    case FAILED = 'FAILED';    // final
}
