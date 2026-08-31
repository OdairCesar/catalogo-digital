<?php

namespace App\Mail;

use App\Models\ToolSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewToolSubmissionCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ToolSubmission $submission,
        public readonly string $toolTitle,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nova submissão em \"{$this->toolTitle}\": {$this->submission->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tools.new-tool-submission-completed',
        );
    }
}
