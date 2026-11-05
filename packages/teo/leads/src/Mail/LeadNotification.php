<?php

namespace Teo\Leads\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Teo\Leads\Models\Lead;

class LeadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Lead $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новая заявка с сайта: ' . $this->lead->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'leads::emails.lead-notification',
            with: ['lead' => $this->lead],
        );
    }
}
