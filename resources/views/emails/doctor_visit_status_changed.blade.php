@component('mail::message')
# Visit Request Update

Dear {{ $visitRequest->patient?->full_name ?? 'Valued Patient' }},

Your doctor visit request has been **{{ ucfirst($visitRequest->status) }}**.

@component('mail::panel')
**Reference Number:** {{ $visitRequest->reference_number }}
**Preferred Date:** {{ $visitRequest->preferred_date }}
**Status:** {{ ucfirst($visitRequest->status) }}
@endcomponent

@if($visitRequest->status === 'confirmed')
Your request has been confirmed. Our team will contact you shortly to coordinate the home visit details.
@elseif($visitRequest->status === 'completed')
Your doctor visit has been marked as completed. Thank you for using Espitalia.
@elseif($visitRequest->status === 'cancelled')
We regret to inform you that your visit request has been cancelled.
@if($visitRequest->cancellation_reason)

**Reason:** {{ $visitRequest->cancellation_reason }}
@endif
If you have any questions, please contact our support team.
@endif

@component('mail::button', ['url' => config('app.url')])
View Your Requests
@endcomponent

Thank you for choosing Espitalia.

{{ config('app.name') }}
@endcomponent
