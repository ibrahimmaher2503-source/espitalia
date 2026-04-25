@php
    $notificationData = is_array($notification->data)
        ? $notification->data
        : json_decode($notification->data, true);

    $group    = $notificationData['data']['notification_group'] ?? null;
    $subject  = $notificationData['subject'] ?? '';
    $notifType = $notificationData['data']['notification_type'] ?? null;
    $notifId  = $notificationData['data']['id'] ?? null;
    $notifMsg = $notificationData['data']['notification_msg'] ?? null;

    // Icon variant
    $iconClass = match($group) {
        'appointment'    => 'pp-notif-icon-appt',
        'wallet'         => 'pp-notif-icon-wallet',
        'requestservice' => 'pp-notif-icon-service',
        default          => 'pp-notif-icon-default',
    };
    $iconName = match($group) {
        'appointment'    => 'ph-calendar-check',
        'wallet'         => 'ph-wallet',
        'requestservice' => 'ph-heart-pulse',
        default          => 'ph-bell',
    };

    // Link
    $notifLink = '#';
    if ($group === 'appointment' && $notifId) {
        $notifLink = route('appointment-details', ['id' => $notifId, 'notification_id' => $notification->id]);
    } elseif ($group === 'wallet') {
        $notifLink = route('wallet-history');
    }

    // Message body
    if ($notifMsg && in_array($notifType, ['wallet_refund', 'cancel_appointment'])) {
        $message = $notifMsg;
    } else {
        $template = \Modules\NotificationTemplate\Models\NotificationTemplateContentMapping::where(
            'subject', $subject
        )->first();
        $message = $template->notification_message ?? '';
    }

    $isUnread = is_null($notification->read_at);
@endphp

<li class="pp-notif-item {{ $isUnread ? 'unread' : '' }}">

    {{-- Icon --}}
    <div class="pp-notif-icon {{ $iconClass }}" aria-hidden="true">
        <i class="ph {{ $iconName }}"></i>
    </div>

    {{-- Body --}}
    <div class="pp-notif-body">
        <a href="{{ $notifLink }}" class="pp-notif-subject">
            {{ $subject ?: ($group ? ucfirst($group) . ' Notification' : __('messages.notification')) }}
        </a>
        @if($message)
            <p class="pp-notif-msg">{{ $message }}</p>
        @endif
    </div>

    {{-- Timestamp + unread dot --}}
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0">
        <span class="pp-notif-time">{{ $notification->created_at->diffForHumans() }}</span>
        @if($isUnread)
            <span class="pp-unread-dot" role="status" aria-label="{{ __('messages.unread') }}"></span>
        @endif
    </div>

</li>
