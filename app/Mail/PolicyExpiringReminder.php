<?php

namespace App\Mail;

use App\Models\Policy;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PolicyExpiringReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Policy $policy,
        public int $daysUntilExpiry
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Policy Renewal Reminder - {$this->policy->policy_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.policy-expiring',
            with: [
                'policy' => $this->policy,
                'daysUntilExpiry' => $this->daysUntilExpiry,
                'client' => $this->policy->client,
            ],
        );
    }
}
