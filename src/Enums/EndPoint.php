<?php

namespace AndyDefer\PhpPawapay\Enums;

enum Endpoint: string
{
    // Deposits
    case DEPOSITS_INITIATE = '/v2/deposits';
    case DEPOSITS_STATUS = '/v2/deposits/{depositId}';
    case DEPOSITS_RESEND_CALLBACK = '/v2/deposits/resend-callback/{depositId}';

    // Payouts
    case PAYOUTS_INITIATE = '/v2/payouts';
    case PAYOUTS_BULK = '/v2/payouts/bulk';
    case PAYOUTS_STATUS = '/v2/payouts/{payoutId}';
    case PAYOUTS_RESEND_CALLBACK = '/v2/payouts/resend-callback/{payoutId}';
    case PAYOUTS_FAIL_ENQUEUED = '/v2/payouts/fail-enqueued/{payoutId}';

    // Refunds
    case REFUNDS_INITIATE = '/v2/refunds';
    case REFUNDS_STATUS = '/v2/refunds/{refundId}';
    case REFUNDS_RESEND_CALLBACK = '/v2/refunds/resend-callback/{refundId}';
    case REFUNDS_FAIL_ENQUEUED = '/v2/refunds/fail-enqueued/{refundId}';

    // Remittances
    case REMITTANCES_INITIATE = '/v2/remittances';
    case REMITTANCES_BULK = '/v2/remittances/bulk';
    case REMITTANCES_STATUS = '/v2/remittances/{remittanceId}';
    case REMITTANCES_RESEND_CALLBACK = '/v2/remittances/resend-callback/{remittanceId}';
    case REMITTANCES_FAIL_ENQUEUED = '/v2/remittances/fail-enqueued/{remittanceId}';

    // Toolkit / Finances
    case AVAILABILITY = '/v2/availability';
    case PREDICT_PROVIDER = '/v2/predict-provider';
    case ACTIVE_CONFIG = '/v2/active-conf';
    case WALLET_BALANCES = '/v2/wallet-balances';
    case STATEMENTS_CREATE = '/v2/statements';
    case STATEMENTS_GET = '/v2/statements/{statementId}';
    case PUBLIC_KEY_HTTP = '/v2/public-key/http';
    case PAYMENT_PAGE = '/v2/paymentpage';

    public function method(): HttpMethod
    {
        return match ($this) {
            self::DEPOSITS_STATUS,
            self::PAYOUTS_STATUS,
            self::REFUNDS_STATUS,
            self::REMITTANCES_STATUS,
            self::AVAILABILITY,
            self::PREDICT_PROVIDER,
            self::ACTIVE_CONFIG,
            self::WALLET_BALANCES,
            self::STATEMENTS_GET,
            self::PUBLIC_KEY_HTTP => HttpMethod::GET,
            default => HttpMethod::POST
        };
    }
}
