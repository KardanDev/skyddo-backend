<?php

namespace App\Events;

use App\Models\Claim;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClaimStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Claim $claim,
        public string $previousStatus
    ) {}
}
