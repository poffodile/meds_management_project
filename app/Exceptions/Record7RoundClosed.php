<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A round closed after the worker opened the medicine screen but before their
 * scheduled answer was committed.
 *
 * This is an expected concurrency outcome, not a server fault. Rendering it
 * back through Record7's ordinary error flash means the worker is told to
 * reopen the round rather than meeting a generic 500 page. The database/model
 * guard remains the control; this class only changes how that refusal is shown.
 */
class Record7RoundClosed extends RuntimeException
{
    public function render($request)
    {
        return back()->with('r7.error', $this->getMessage());
    }
}
