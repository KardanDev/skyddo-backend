<?php

namespace App\Mail;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Claim $claim
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Claim Update - {$this->claim->claim_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.claim-status',
            with: [
                'claim' => $this->claim,
                'client' => $this->claim->client,
                'policy' => $this->claim->policy,
            ],
        );
    }
}
