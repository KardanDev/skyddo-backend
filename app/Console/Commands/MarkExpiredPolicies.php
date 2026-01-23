<?php

namespace App\Console\Commands;

use App\Services\PolicyService;
use Illuminate\Console\Command;

class MarkExpiredPolicies extends Command
{
    protected $signature = 'policies:mark-expired';

    protected $description = 'Mark policies that have passed their end date as expired';

    public function handle(PolicyService $policyService): int
    {
        $count = $policyService->markExpired();

        $this->info("Marked {$count} policies as expired.");

        return Command::SUCCESS;
    }
}
