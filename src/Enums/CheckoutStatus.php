<?php

namespace AndyDefer\PhpPawapay\Enums;

enum CheckoutStatus: string
{
    case WAITING_PAYMENT = 'WAITING_PAYMENT'; // en attente du paiement client
    case PROCESSING = 'PROCESSING';       // tentative de paiement en cours
    case COMPLETED = 'COMPLETED';        // final : succès
    case FAILED = 'FAILED';           // final : échec
    case EXPIRED = 'EXPIRED';          // final : expiré
    case CANCELLED = 'CANCELLED';        // final : annulé par le client
}
