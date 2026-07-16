<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Responses;

use AndyDefer\DomainStructures\Normalizers\NormalizerChain;
use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Utils\EmptyStruct;
use AndyDefer\PhpPawapay\Enums\DepositSearchStatus;
use AndyDefer\PhpPawapay\Structures\DepositDataStruct;
use AndyDefer\PhpPawapay\Structures\FailureReasonStruct;

final class CheckDepositStatusResponse extends Response
{
    public function getSearchStatus(): DepositSearchStatus
    {
        $data = $this->getBody()->format();

        return DepositSearchStatus::tryFrom($data['status'] ?? '') ?? DepositSearchStatus::NOT_FOUND;
    }

    public function getDepositData(): ?DepositDataStruct
    {
        $data = $this->getBody()->format();

        if (! isset($data['data']) || $this->getSearchStatus()->isNotFound()) {
            return null;
        }

        return DepositDataStruct::from($data['data']);
    }

    public function isFound(): bool
    {
        return $this->getSearchStatus()->isFound();
    }

    public function isNotFound(): bool
    {
        return $this->getSearchStatus()->isNotFound();
    }

    public function hasFailureReason(): bool
    {
        $data = NormalizerChain::get(true)->normalize($this->getBody()->format());

        return isset($data['failureReason']) || (isset($data['data']['failureReason']));
    }

    public function getFailureReason(): ?FailureReasonStruct
    {
        $data = $this->getBody()->format();

        if (isset($data['failureReason'])) {
            return FailureReasonStruct::from($data['failureReason']);
        }

        if (isset($data['data']['failureReason'])) {
            return FailureReasonStruct::from($data['data']['failureReason']);
        }

        return null;
    }

    public static function getStructClass(): string
    {
        return EmptyStruct::class;
    }
}
