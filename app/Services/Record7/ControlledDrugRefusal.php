<?php

namespace App\Services\Record7;

use RuntimeException;

/**
 * A controlled drug movement Record7 declined to record.
 *
 * It carries the reason out of the transaction that was rolled back, so the
 * refusal can be audited after the unwind. Without that, the record of a
 * blocked attempt on a controlled drug disappeared along with the attempt
 * itself — which is the one thing somebody is most likely to ask about later.
 *
 * Named `refusalCode` rather than `code` because Exception already has a `code`
 * and it means something else entirely.
 */
class ControlledDrugRefusal extends RuntimeException
{
    public function __construct(string $message, public readonly string $refusalCode)
    {
        parent::__construct($message);
    }
}
