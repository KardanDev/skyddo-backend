<?php

namespace App\Mail;

use App\Models\CompanySetting;
use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        $companyName = CompanySetting::first()?->company_name ?? config('app.name');

        return new Envelope(
            subject: "You've Been Invited to Join {$companyName}",
        );
    }

    public function content(): Content
    {
        $companySettings = CompanySetting::first();

        // Use short link if available, otherwise fall back to full token URL
        $registerUrl = $this->invitation->short_code
            ? config('app.url').'/api/i/'.$this->invitation->short_code
            : config('app.frontend_url', config('app.url')).'/register?token='.$this->invitation->token;

        return new Content(
            markdown: 'emails.invitation',
            with: [
                'registerUrl' => $registerUrl,
                'invitation' => $this->invitation,
                'inviter' => $this->invitation->inviter,
                'companySettings' => $companySettings,
            ],
        );
    }
}
