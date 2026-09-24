<?php

namespace AndyDefer\PhpPawapay\Enums;

enum RefundStatus: string
{
    case ACCEPTED = 'ACCEPTED';         // accepté pour traitement
    case ENQUEUED = 'ENQUEUED';          // accepté mais mis en file d'attente
    case PROCESSING = 'PROCESSING';        // soumis au fournisseur, en cours
    case IN_RECONCILIATION = 'IN_RECONCILIATION'; // en réconciliation pour déterminer le statut final
    case COMPLETED = 'COMPLETED'; // final : succès
    case FAILED = 'FAILED';    // final : échec, voir failureReason
}
