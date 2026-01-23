<?php

namespace App\Console\Commands;

use App\Events\PolicyExpiring;
use App\Mail\PolicyExpiringReminder;
use App\Models\Policy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendRenewalReminders extends Command
{
    protected $signature = 'policies:send-renewal-reminders {--days=45,30,7 : Days before expiry to send reminders}';

    protected $description = 'Send renewal reminders for expiring policies';

    public function handle(): int
    {
        $daysOption = $this->option('days');
        $reminderDays = array_map('intval', explode(',', $daysOption));

        foreach ($reminderDays as $days) {
            $this->sendRemindersForDay($days);
        }

        return Command::SUCCESS;
    }

    private function sendRemindersForDay(int $days): void
    {
        $expiryDate = now()->addDays($days)->toDateString();

        $policies = Policy::where('status', 'active')
            ->whereDate('end_date', $expiryDate)
            ->with(['client', 'insurer'])
            ->get();

        $count = 0;

        foreach ($policies as $policy) {
            if (! $policy->client->email) {
                continue;
            }

            Mail::to($policy->client->email)
                ->queue(new PolicyExpiringReminder($policy, $days));

            PolicyExpiring::dispatch($policy, $days);

            $count++;
        }

        $this->info("Sent {$count} renewal reminders for policies expiring in {$days} days.");
    }
}
