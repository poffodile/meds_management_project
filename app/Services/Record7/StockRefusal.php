<?php

namespace App\Services\Record7;

use RuntimeException;

/**
 * An ordinary stock movement Record7 declined to record.
 *
 * It carries the reason out of the transaction that was rolled back, so the
 * refusal can be audited after the unwind. Without that, the record of a
 * blocked stock act disappears along with the act itself — which is the one
 * thing somebody is most likely to ask about afterwards.
 *
 * Named `refusalCode` rather than `code` because Exception already has a `code`
 * and it means something else entirely. Same shape as ControlledDrugRefusal, so
 * there is one pattern for this in Record7 and not two.
 */
class StockRefusal extends RuntimeException
{
    public function __construct(string $message, public readonly string $refusalCode)
    {
        parent::__construct($message);
    }
}
