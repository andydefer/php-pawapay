<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\Builders;

use AndyDefer\PhpPawapay\Builders\ResendDepositCallbackBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;
use PHPUnit\Framework\TestCase;

final class ResendDepositCallbackBuilderTest extends TestCase
{
    private string $apiToken = 'test-token';

    public function test_builder_creates_client_with_default_values(): void
    {
        $builder = ResendDepositCallbackBuilder::create($this->apiToken);
        $client = $builder->build();

        $this->assertInstanceOf(PawapayClient::class, $client);
    }

    public function test_builder_with_base_url(): void
    {
        $builder = ResendDepositCallbackBuilder::create($this->apiToken)
            ->withBaseUrl(PawaPayBaseUrl::PRODUCTION);

        $client = $builder->build();

        $this->assertInstanceOf(PawapayClient::class, $client);
    }

    public function test_builder_with_deposit_id(): void
    {
        $depositId = '9b724dbf-32a7-4e63-96bb-59a4747e43ca';

        $builder = ResendDepositCallbackBuilder::create($this->apiToken)
            ->withDepositId($depositId);

        $reflection = new \ReflectionClass($builder);
        $property = $reflection->getProperty('depositId');

        $this->assertEquals($depositId, $property->getValue($builder));
    }

    public function test_builder_create_returns_new_instance(): void
    {
        $builder1 = ResendDepositCallbackBuilder::create($this->apiToken);
        $builder2 = ResendDepositCallbackBuilder::create($this->apiToken);

        $this->assertNotSame($builder1, $builder2);
    }
}
