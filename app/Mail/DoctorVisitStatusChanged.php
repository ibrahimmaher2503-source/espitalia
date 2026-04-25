<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\DoctorVisit\Models\DoctorVisitRequest;

class DoctorVisitStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly DoctorVisitRequest $visitRequest
    ) {}

    public function envelope(): Envelope
    {
        $statusLabel = match ($this->visitRequest->status) {
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
            default     => ucfirst($this->visitRequest->status),
        };

        return new Envelope(
            subject: "[Espitalia] Visit Request {$this->visitRequest->reference_number} — {$statusLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.doctor_visit_status_changed',
            with: ['visitRequest' => $this->visitRequest],
        );
    }
}
