<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommonMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public array $contentData = [
            'title' => '',
            'message' => '',
        ],
        protected array $attachmentsData = [],
        protected array $envelopeData = [
            'subject' => null,
            'from' => null,
            'fromName' => null,
            'replyTo' => null,
            'replyToName' => null,
            'metadata' => [],
            'tags' => [],
        ],
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                $this->envelopeData['from'] ?? config('mail.from.address'),
                $this->envelopeData['fromName'] ?? config('mail.from.name')
            ),
            replyTo: [
                new Address(
                    $this->envelopeData['replyTo'] ?? config('mail.reply_to.address'),
                    $this->envelopeData['replyToName'] ?? config('mail.reply_to.name')
                ),
            ],
            subject: $this->envelopeData['subject'],
            tags: $this->envelopeData['tags'],
            metadata: $this->envelopeData['metadata'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.common',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return array_map(fn ($path) => Attachment::fromStorageDisk('public', $path), $this->attachmentsData);
    }
}
