<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\Builders;

use AndyDefer\PhpPawapay\Builders\CheckDepositStatusBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;
use PHPUnit\Framework\TestCase;

final class CheckDepositStatusBuilderTest extends TestCase
{
    private string $apiToken = 'test-token';

    public function test_builder_creates_client_with_default_values(): void
    {
        $builder = CheckDepositStatusBuilder::create($this->apiToken);
        $client = $builder->build();

        $this->assertInstanceOf(PawapayClient::class, $client);
    }

    public function test_builder_with_base_url(): void
    {
        $builder = CheckDepositStatusBuilder::create($this->apiToken)
            ->withBaseUrl(PawaPayBaseUrl::PRODUCTION);

        $client = $builder->build();

        $this->assertInstanceOf(PawapayClient::class, $client);
    }

    public function test_builder_with_deposit_id(): void
    {
        $depositId = '60bd6a3d-177e-4ec2-a65c-d622ede29c99';

        $builder = CheckDepositStatusBuilder::create($this->apiToken)
            ->withDepositId($depositId);

        $reflection = new \ReflectionClass($builder);
        $property = $reflection->getProperty('depositId');

        $this->assertEquals($depositId, $property->getValue($builder));
    }

    public function test_builder_create_returns_new_instance(): void
    {
        $builder1 = CheckDepositStatusBuilder::create($this->apiToken);
        $builder2 = CheckDepositStatusBuilder::create($this->apiToken);

        $this->assertNotSame($builder1, $builder2);
    }
}
