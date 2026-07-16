<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Tests\Unit\ValueObjects;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpPawapay\Enums\Currency;
use AndyDefer\PhpPawapay\Enums\PayerType;
use AndyDefer\PhpPawapay\Enums\Provider;
use AndyDefer\PhpPawapay\ValueObjects\AccountDetailsVO;
use AndyDefer\PhpPawapay\ValueObjects\AmountVO;
use AndyDefer\PhpPawapay\ValueObjects\InitiateDepositVO;
use AndyDefer\PhpPawapay\ValueObjects\MessageVO;
use AndyDefer\PhpPawapay\ValueObjects\MetadataVO;
use AndyDefer\PhpPawapay\ValueObjects\PayerVO;
use AndyDefer\PhpPawapay\ValueObjects\PhoneNumberVO;
use AndyDefer\PhpPawapay\ValueObjects\ReferenceVO;
use AndyDefer\PhpPawapay\ValueObjects\UuidVO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MetadataVOTest extends TestCase
{
    public function test_metadata_vo_accepts_valid_metadata(): void
    {
        $data = new StrictDataObject([
            'orderId' => 'ORD-123456',
            'customerId' => 'CUST-789',
            'product' => 'Premium Plan',
        ]);

        $metadata = new MetadataVO($data);

        $this->assertNotNull($metadata->getValue());
        $this->assertSame(3, $metadata->count());
        $this->assertSame('ORD-123456', $metadata->get('orderId'));
        $this->assertTrue($metadata->has('product'));
        $this->assertFalse($metadata->isEmpty());
    }

    public function test_metadata_vo_accepts_null(): void
    {
        $metadata = new MetadataVO(null);

        $this->assertNull($metadata->getValue());
        $this->assertSame(0, $metadata->count());
        $this->assertTrue($metadata->isEmpty());
    }

    public function test_metadata_vo_accepts_int_values(): void
    {
        $data = new StrictDataObject([
            'quantity' => 5,
            'price' => 100,
        ]);

        $metadata = new MetadataVO($data);

        $this->assertSame(5, $metadata->get('quantity'));
        $this->assertSame(100, $metadata->get('price'));
    }

    public function test_metadata_vo_accepts_float_values(): void
    {
        $data = new StrictDataObject([
            'discount' => 10.5,
            'tax' => 7.5,
        ]);

        $metadata = new MetadataVO($data);

        $this->assertSame(10.5, $metadata->get('discount'));
        $this->assertSame(7.5, $metadata->get('tax'));
    }

    public function test_metadata_vo_accepts_bool_values(): void
    {
        $data = new StrictDataObject([
            'isActive' => true,
            'isVerified' => false,
        ]);

        $metadata = new MetadataVO($data);

        $this->assertTrue($metadata->get('isActive'));
        $this->assertFalse($metadata->get('isVerified'));
    }

    public function test_metadata_vo_rejects_array_as_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metadata value for key "items" must be a scalar');

        $data = new StrictDataObject([
            'items' => ['item1', 'item2'],
        ]);

        new MetadataVO($data);
    }

    public function test_metadata_vo_rejects_object_as_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metadata value for key "obj" must be a scalar');

        $data = new StrictDataObject([
            'obj' => new \stdClass,
        ]);

        new MetadataVO($data);
    }

    public function test_metadata_vo_rejects_more_than_10_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metadata cannot exceed 10 fields');

        $dataArray = [];
        for ($i = 1; $i <= 11; $i++) {
            $dataArray["field_{$i}"] = "value_{$i}";
        }

        $data = new StrictDataObject($dataArray);
        new MetadataVO($data);
    }

    public function test_metadata_vo_rejects_non_string_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metadata key must be a string');

        $dataArray = [];
        $dataArray[123] = 'value';

        $data = new StrictDataObject($dataArray);
        new MetadataVO($data);
    }

    public function test_metadata_vo_accepts_exactly_10_fields(): void
    {
        $dataArray = [];
        for ($i = 1; $i <= 10; $i++) {
            $dataArray["field_{$i}"] = "value_{$i}";
        }

        $data = new StrictDataObject($dataArray);
        $metadata = new MetadataVO($data);

        $this->assertSame(10, $metadata->count());
        $this->assertSame('value_1', $metadata->get('field_1'));
        $this->assertSame('value_10', $metadata->get('field_10'));
    }

    public function test_metadata_vo_has_returns_false_for_nonexistent_key(): void
    {
        $data = new StrictDataObject([
            'orderId' => 'ORD-123',
        ]);

        $metadata = new MetadataVO($data);

        $this->assertFalse($metadata->has('nonexistent'));
    }

    public function test_metadata_vo_get_returns_null_for_nonexistent_key(): void
    {
        $data = new StrictDataObject([
            'orderId' => 'ORD-123',
        ]);

        $metadata = new MetadataVO($data);

        $this->assertNull($metadata->get('nonexistent'));
    }

    public function test_metadata_vo_to_array_returns_null_when_empty(): void
    {
        $metadata = new MetadataVO(null);

        $this->assertNull($metadata->toArray());
    }

    public function test_metadata_vo_to_array_returns_array_when_not_empty(): void
    {
        $data = new StrictDataObject([
            'orderId' => 'ORD-123',
        ]);

        $metadata = new MetadataVO($data);

        $this->assertSame(['orderId' => 'ORD-123'], $metadata->toArray());
    }

    public function test_metadata_vo_can_be_used_in_deposit_struct(): void
    {
        $metadata = new MetadataVO(
            new StrictDataObject([
                'orderId' => 'ORD-123456',
                'customerId' => 'CUST-789',
            ])
        );

        $deposit = new InitiateDepositVO(
            depositId: new UuidVO('f4401bd2-1568-4140-bf2d-eb77d2b2b639'),
            payer: new PayerVO(
                type: PayerType::MMO,
                accountDetails: new AccountDetailsVO(
                    phoneNumber: new PhoneNumberVO('260763456789'),
                    provider: Provider::MTN_MOMO_ZMB,
                )
            ),
            amount: new AmountVO(15.00),
            currency: Currency::ZMW,
            preAuthorisationCode: null,
            clientReferenceId: new ReferenceVO('INV-123456'),
            customerMessage: new MessageVO('Payment for order #123456'),
            metadata: $metadata,
        );

        $this->assertNotNull($deposit->metadata);
        $this->assertSame(2, $deposit->metadata->count());
        $this->assertSame('ORD-123456', $deposit->metadata->get('orderId'));
    }
}
