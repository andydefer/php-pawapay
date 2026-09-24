<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpPawapay\Structures\DepositDataStruct;

/**
 * Collection of DepositDataStruct instances.
 *
 * @extends AbstractTypedCollection<DepositDataStruct>
 */
final class DepositDataStructCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(DepositDataStruct::class);
    }
}
