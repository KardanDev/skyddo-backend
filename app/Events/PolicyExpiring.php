<?php

namespace App\Events;

use App\Models\Policy;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyExpiring
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Policy $policy,
        public int $daysUntilExpiry
    ) {}
}
