<?php

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  string|null  $fromAddress  Override the sender address (e.g. for Outlook SMTP).
     */
    public function __construct(
        public string $otp,
        public ?string $fromAddress = null,
        public ?string $fromName = null,
    ) {}

    public function withFromAddress(string $address, ?string $name = null): self
    {
        $this->fromAddress = $address;
        $this->fromName = $name;

        return $this;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $from = $this->fromAddress
            ? new Address($this->fromAddress, $this->fromName ?? config('mail.from.name', 'UEConnect'))
            : null;

        return new Envelope(
            subject: 'Mã xác nhận đặt lại mật khẩu - UEConnect',
            from: $from,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.reset-password-otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
