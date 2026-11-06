<?php

namespace App\Services\Estimation;

use RuntimeException;

class PreviewChangedException extends RuntimeException
{
    public array $snapshot;

    public function __construct(array $snapshot)
    {
        parent::__construct('Inventory, commitments, or recipes changed. Review the refreshed plan before saving again.');
        $this->snapshot = $snapshot;
    }
}
