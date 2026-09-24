<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\Builders;

use AndyDefer\PhpPawapay\Builders\PredictProviderBuilder;
use AndyDefer\PhpPawapay\Enums\PawaPayBaseUrl;
use AndyDefer\PhpPawapay\PawapayClient;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PredictProviderBuilderTest extends TestCase
{
    public function test_builder_creates_client_with_token(): void
    {
        $builder = PredictProviderBuilder::create('test-token');

        $client = $builder->build();

        $this->assertInstanceOf(PawapayClient::class, $client);

        $reflection = new ReflectionClass($client);
        $apiToken = $reflection->getProperty('apiToken');

        $this->assertSame('test-token', $apiToken->getValue($client));
    }

    public function test_builder_defaults_to_sandbox(): void
    {
        $builder = PredictProviderBuilder::create('test-token');

        $client = $builder->build();

        $reflection = new ReflectionClass($client);
        $baseUrl = $reflection->getProperty('baseUrl');

        $this->assertSame(PawaPayBaseUrl::SANDBOX, $baseUrl->getValue($client));
    }

    public function test_builder_with_base_url_production(): void
    {
        $builder = PredictProviderBuilder::create('test-token')
            ->withBaseUrl(PawaPayBaseUrl::PRODUCTION);

        $client = $builder->build();

        $reflection = new ReflectionClass($client);
        $baseUrl = $reflection->getProperty('baseUrl');

        $this->assertSame(PawaPayBaseUrl::PRODUCTION, $baseUrl->getValue($client));
    }

    public function test_builder_with_phone_number(): void
    {
        $builder = PredictProviderBuilder::create('test-token')
            ->withPhoneNumber('260763456789');

        $reflection = new ReflectionClass($builder);
        $phoneNumber = $reflection->getProperty('phoneNumber');

        $this->assertInstanceOf(PhoneNumberVO::class, $phoneNumber->getValue($builder));
        $this->assertSame('260763456789', $phoneNumber->getValue($builder)->getValue());
    }

    public function test_builder_chaining_all_methods(): void
    {
        $builder = PredictProviderBuilder::create('test-token')
            ->withBaseUrl(PawaPayBaseUrl::PRODUCTION)
            ->withPhoneNumber('243812345678');

        $client = $builder->build();

        $reflection = new ReflectionClass($client);
        $baseUrl = $reflection->getProperty('baseUrl');

        $this->assertSame(PawaPayBaseUrl::PRODUCTION, $baseUrl->getValue($client));
    }

    public function test_builder_returns_self_for_chaining(): void
    {
        $builder = PredictProviderBuilder::create('test-token');

        $this->assertSame($builder, $builder->withBaseUrl(PawaPayBaseUrl::SANDBOX));
        $this->assertSame($builder, $builder->withPhoneNumber('260763456789'));
    }
}
