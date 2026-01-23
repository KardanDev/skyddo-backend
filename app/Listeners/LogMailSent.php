<?php

namespace App\Listeners;

use App\Models\CommunicationLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class LogMailSent
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $message = $event->message;

        // Extract recipient email
        $to = $message->getTo();
        $recipient = ! empty($to) ? array_key_first($to) : null;

        // Extract subject
        $subject = $message->getSubject();

        // Extract body
        $body = $message->getBody();

        // Try to determine related entity from mailable
        $communicableType = null;
        $communicableId = null;

        // Check for various entity types in the mailable data
        if (isset($event->data['quote'])) {
            $communicableType = get_class($event->data['quote']);
            $communicableId = $event->data['quote']->id;
        } elseif (isset($event->data['policy'])) {
            $communicableType = get_class($event->data['policy']);
            $communicableId = $event->data['policy']->id;
        } elseif (isset($event->data['claim'])) {
            $communicableType = get_class($event->data['claim']);
            $communicableId = $event->data['claim']->id;
        } elseif (isset($event->data['invoice'])) {
            $communicableType = get_class($event->data['invoice']);
            $communicableId = $event->data['invoice']->id;
        } elseif (isset($event->data['invitation'])) {
            $communicableType = get_class($event->data['invitation']);
            $communicableId = $event->data['invitation']->id;
        }

        CommunicationLog::create([
            'communicable_type' => $communicableType,
            'communicable_id' => $communicableId,
            'channel' => 'email',
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => is_string($body) ? substr($body, 0, 1000) : null, // Limit body length
            'status' => 'sent',
            'sent_at' => now(),
            'triggered_by' => Auth::id(),
        ]);
    }
}
