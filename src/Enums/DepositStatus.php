<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Enums;

enum DepositStatus: string
{
    // Statuts d'initiation (réponse immédiate)
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';
    case DUPLICATE_IGNORED = 'DUPLICATE_IGNORED';

    // Statuts de suivi (polling / callback)
    case PROCESSING = 'PROCESSING';
    case IN_RECONCILIATION = 'IN_RECONCILIATION';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';

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

    public function isProcessing(): bool
    {
        return $this === self::PROCESSING;
    }

    public function isInReconciliation(): bool
    {
        return $this === self::IN_RECONCILIATION;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::REJECTED]);
    }

    public function isPending(): bool
    {
        return in_array($this, [self::ACCEPTED, self::PROCESSING, self::IN_RECONCILIATION]);
    }
}
