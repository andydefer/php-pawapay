<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpPawapay\Enums\Language;

/**
 * Collection of Language enums.
 *
 * @extends AbstractTypedCollection<Language>
 */
final class LanguageCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(Language::class);
    }

    public static function default(): self
    {
        $collection = new self;
        $collection->add(Language::EN);

        return $collection;
    }

    /**
     * @return array<string>
     */
    public function toValues(): array
    {
        return $this->map(fn (Language $language) => $language->value)->toArray();
    }
}
