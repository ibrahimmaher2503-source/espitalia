<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Constant\Models\Constant;
use Modules\NotificationTemplate\Models\NotificationTemplate;

return new class extends Migration
{
    private array $types = [
        'new_nurse_request',
        'nurse_assigned',
        'nurse_request_confirmed',
        'nurse_request_in_progress',
        'nurse_request_completed',
        'nurse_request_cancelled',
        'nurse_request_cancelled_by_admin',
    ];

    public function up(): void
    {
        // 1. New nurse request — notify admin
        $t = NotificationTemplate::firstOrCreate(
            ['type' => 'new_nurse_request'],
            [
                'name'     => 'new_nurse_request',
                'label'    => 'New Nurse Request Received',
                'status'   => 1,
                'to'       => json_encode(['admin']),
                'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ]
        );
        $t->defaultNotificationTemplateMap()->firstOrCreate(['language' => 'en', 'user_type' => 'admin'], [
            'language'               => 'en',
            'user_type'              => 'admin',
            'status'                 => 1,
            'subject'                => 'New Nurse Request Received',
            'mail_subject'           => 'New Nurse Request Received',
            'sms_subject'            => 'New Nurse Request Received',
            'whatsapp_subject'       => 'New Nurse Request Received',
            'notification_message'   => 'A new nurse request has been submitted and is waiting for assignment.',
            'notification_link'      => '',
            'template_detail'        => '<p>A new nurse request has been received.</p><p>Patient: [[ user_name ]]<br>Preferred Date: [[ preferred_date ]]<br>Description: [[ description ]]</p>',
            'mail_template_detail'   => '<p>A new nurse request has been received.</p><p>Patient: [[ user_name ]]<br>Preferred Date: [[ preferred_date ]]<br>Description: [[ description ]]</p>',
            'sms_template_detail'    => 'New nurse request from [[ user_name ]] on [[ preferred_date ]]. Please review in the dashboard.',
            'whatsapp_template_detail' => 'New nurse request from [[ user_name ]] on [[ preferred_date ]]. Please review in the dashboard.',
        ]);

        // 2. Nurse assigned — notify patient + nurse
        $t = NotificationTemplate::firstOrCreate(
            ['type' => 'nurse_assigned'],
            [
                'name'     => 'nurse_assigned',
                'label'    => 'Nurse Assigned to Request',
                'status'   => 1,
                'to'       => json_encode(['user', 'nurse']),
                'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ]
        );
        $t->defaultNotificationTemplateMap()->firstOrCreate(['language' => 'en', 'user_type' => 'user'], [
            'language'               => 'en',
            'user_type'              => 'user',
            'status'                 => 1,
            'subject'                => 'A Nurse Has Been Assigned to Your Request',
            'mail_subject'           => 'A Nurse Has Been Assigned to Your Request',
            'sms_subject'            => 'Nurse Assigned',
            'whatsapp_subject'       => 'Nurse Assigned',
            'notification_message'   => 'A nurse has been assigned to your request.',
            'notification_link'      => '',
            'template_detail'        => '<p>Good news! A nurse has been assigned to your request.</p><p>Preferred Date: [[ preferred_date ]]</p><p>Please be available at the given address on the scheduled date.</p>',
            'mail_template_detail'   => '<p>Good news! A nurse has been assigned to your request.</p><p>Preferred Date: [[ preferred_date ]]</p><p>Please be available at the given address on the scheduled date.</p>',
            'sms_template_detail'    => 'A nurse has been assigned to your request on [[ preferred_date ]]. Please be ready.',
            'whatsapp_template_detail' => 'A nurse has been assigned to your request on [[ preferred_date ]]. Please be ready.',
        ]);
        $t->defaultNotificationTemplateMap()->firstOrCreate(['language' => 'en', 'user_type' => 'nurse'], [
            'language'               => 'en',
            'user_type'              => 'nurse',
            'status'                 => 1,
            'subject'                => 'You Have Been Assigned to a Nurse Request',
            'mail_subject'           => 'You Have Been Assigned to a Nurse Request',
            'sms_subject'            => 'New Assignment',
            'whatsapp_subject'       => 'New Assignment',
            'notification_message'   => 'You have been assigned to a new nurse request.',
            'notification_link'      => '',
            'template_detail'        => '<p>You have been assigned to a new nurse request.</p><p>Patient: [[ user_name ]]<br>Preferred Date: [[ preferred_date ]]<br>Description: [[ description ]]</p>',
            'mail_template_detail'   => '<p>You have been assigned to a new nurse request.</p><p>Patient: [[ user_name ]]<br>Preferred Date: [[ preferred_date ]]<br>Description: [[ description ]]</p>',
            'sms_template_detail'    => 'You have been assigned to a nurse request for [[ user_name ]] on [[ preferred_date ]].',
            'whatsapp_template_detail' => 'You have been assigned to a nurse request for [[ user_name ]] on [[ preferred_date ]].',
        ]);

        // 3. Request confirmed — notify patient
        $t = NotificationTemplate::firstOrCreate(
            ['type' => 'nurse_request_confirmed'],
            [
                'name'     => 'nurse_request_confirmed',
                'label'    => 'Nurse Request Confirmed',
                'status'   => 1,
                'to'       => json_encode(['user']),
                'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ]
        );
        $t->defaultNotificationTemplateMap()->firstOrCreate(['language' => 'en', 'user_type' => 'user'], [
            'language'               => 'en',
            'user_type'              => 'user',
            'status'                 => 1,
            'subject'                => 'Your Nurse Request Has Been Confirmed',
            'mail_subject'           => 'Your Nurse Request Has Been Confirmed',
            'sms_subject'            => 'Request Confirmed',
            'whatsapp_subject'       => 'Request Confirmed',
            'notification_message'   => 'Your nurse request has been confirmed.',
            'notification_link'      => '',
            'template_detail'        => '<p>Your nurse request has been confirmed.</p><p>Preferred Date: [[ preferred_date ]]</p><p>The nurse will visit you at your address on the scheduled date.</p>',
            'mail_template_detail'   => '<p>Your nurse request has been confirmed.</p><p>Preferred Date: [[ preferred_date ]]</p><p>The nurse will visit you at your address on the scheduled date.</p>',
            'sms_template_detail'    => 'Your nurse request on [[ preferred_date ]] has been confirmed. The nurse will visit you soon.',
            'whatsapp_template_detail' => 'Your nurse request on [[ preferred_date ]] has been confirmed. The nurse will visit you soon.',
        ]);

        // 4. Request in progress — notify patient
        $t = NotificationTemplate::firstOrCreate(
            ['type' => 'nurse_request_in_progress'],
            [
                'name'     => 'nurse_request_in_progress',
                'label'    => 'Nurse Request In Progress',
                'status'   => 1,
                'to'       => json_encode(['user']),
                'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ]
        );
        $t->defaultNotificationTemplateMap()->firstOrCreate(['language' => 'en', 'user_type' => 'user'], [
            'language'               => 'en',
            'user_type'              => 'user',
            'status'                 => 1,
            'subject'                => 'Your Nurse Is Now On The Way',
            'mail_subject'           => 'Your Nurse Is Now On The Way',
            'sms_subject'            => 'Nurse On The Way',
            'whatsapp_subject'       => 'Nurse On The Way',
            'notification_message'   => 'Your nurse request is now in progress.',
            'notification_link'      => '',
            'template_detail'        => '<p>Your nurse request is now in progress.</p><p>The nurse is on the way to your address. Please be ready to receive the service.</p>',
            'mail_template_detail'   => '<p>Your nurse request is now in progress.</p><p>The nurse is on the way to your address. Please be ready to receive the service.</p>',
            'sms_template_detail'    => 'Your nurse is on the way. Your request is now in progress.',
            'whatsapp_template_detail' => 'Your nurse is on the way. Your request is now in progress.',
        ]);

        // 5. Request completed — notify patient
        $t = NotificationTemplate::firstOrCreate(
            ['type' => 'nurse_request_completed'],
            [
                'name'     => 'nurse_request_completed',
                'label'    => 'Nurse Request Completed',
                'status'   => 1,
                'to'       => json_encode(['user']),
                'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ]
        );
        $t->defaultNotificationTemplateMap()->firstOrCreate(['language' => 'en', 'user_type' => 'user'], [
            'language'               => 'en',
            'user_type'              => 'user',
            'status'                 => 1,
            'subject'                => 'Your Nurse Request Has Been Completed',
            'mail_subject'           => 'Your Nurse Request Has Been Completed',
            'sms_subject'            => 'Request Completed',
            'whatsapp_subject'       => 'Request Completed',
            'notification_message'   => 'Your nurse request has been completed successfully.',
            'notification_link'      => '',
            'template_detail'        => '<p>Your nurse request has been completed successfully.</p><p>Thank you for using our service. We hope you had a great experience.</p>',
            'mail_template_detail'   => '<p>Your nurse request has been completed successfully.</p><p>Thank you for using our service. We hope you had a great experience.</p>',
            'sms_template_detail'    => 'Your nurse request has been completed. Thank you for using our service.',
            'whatsapp_template_detail' => 'Your nurse request has been completed. Thank you for using our service.',
        ]);

        // 6. Cancelled by patient — notify admin + nurse
        $t = NotificationTemplate::firstOrCreate(
            ['type' => 'nurse_request_cancelled'],
            [
                'name'     => 'nurse_request_cancelled',
                'label'    => 'Nurse Request Cancelled by Patient',
                'status'   => 1,
                'to'       => json_encode(['admin', 'nurse']),
                'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ]
        );
        $t->defaultNotificationTemplateMap()->firstOrCreate(['language' => 'en', 'user_type' => 'admin'], [
            'language'               => 'en',
            'user_type'              => 'admin',
            'status'                 => 1,
            'subject'                => 'Nurse Request Cancelled by Patient',
            'mail_subject'           => 'Nurse Request Cancelled by Patient',
            'sms_subject'            => 'Request Cancelled',
            'whatsapp_subject'       => 'Request Cancelled',
            'notification_message'   => 'A nurse request has been cancelled by the patient.',
            'notification_link'      => '',
            'template_detail'        => '<p>A nurse request has been cancelled by the patient.</p><p>Patient: [[ user_name ]]<br>Reason: [[ description ]]</p>',
            'mail_template_detail'   => '<p>A nurse request has been cancelled by the patient.</p><p>Patient: [[ user_name ]]<br>Reason: [[ description ]]</p>',
            'sms_template_detail'    => 'Nurse request from [[ user_name ]] has been cancelled.',
            'whatsapp_template_detail' => 'Nurse request from [[ user_name ]] has been cancelled.',
        ]);
        $t->defaultNotificationTemplateMap()->firstOrCreate(['language' => 'en', 'user_type' => 'nurse'], [
            'language'               => 'en',
            'user_type'              => 'nurse',
            'status'                 => 1,
            'subject'                => 'Nurse Request Cancelled',
            'mail_subject'           => 'Nurse Request Cancelled',
            'sms_subject'            => 'Request Cancelled',
            'whatsapp_subject'       => 'Request Cancelled',
            'notification_message'   => 'A nurse request assigned to you has been cancelled by the patient.',
            'notification_link'      => '',
            'template_detail'        => '<p>A nurse request assigned to you has been cancelled by the patient.</p><p>Reason: [[ description ]]</p>',
            'mail_template_detail'   => '<p>A nurse request assigned to you has been cancelled by the patient.</p><p>Reason: [[ description ]]</p>',
            'sms_template_detail'    => 'A nurse request assigned to you has been cancelled by the patient.',
            'whatsapp_template_detail' => 'A nurse request assigned to you has been cancelled by the patient.',
        ]);

        // 7. Cancelled by admin — notify patient + nurse
        $t = NotificationTemplate::firstOrCreate(
            ['type' => 'nurse_request_cancelled_by_admin'],
            [
                'name'     => 'nurse_request_cancelled_by_admin',
                'label'    => 'Nurse Request Cancelled by Admin',
                'status'   => 1,
                'to'       => json_encode(['user', 'nurse']),
                'channels' => ['IS_MAIL' => '1', 'PUSH_NOTIFICATION' => '1', 'IS_CUSTOM_WEBHOOK' => '0', 'IS_SMS' => '0', 'IS_WHATSAPP' => '0'],
            ]
        );
        $t->defaultNotificationTemplateMap()->firstOrCreate(['language' => 'en', 'user_type' => 'user'], [
            'language'               => 'en',
            'user_type'              => 'user',
            'status'                 => 1,
            'subject'                => 'Your Nurse Request Has Been Cancelled',
            'mail_subject'           => 'Your Nurse Request Has Been Cancelled',
            'sms_subject'            => 'Request Cancelled',
            'whatsapp_subject'       => 'Request Cancelled',
            'notification_message'   => 'Your nurse request has been cancelled by the admin.',
            'notification_link'      => '',
            'template_detail'        => '<p>We regret to inform you that your nurse request has been cancelled.</p><p>Reason: [[ description ]]</p><p>Please contact us if you need further assistance.</p>',
            'mail_template_detail'   => '<p>We regret to inform you that your nurse request has been cancelled.</p><p>Reason: [[ description ]]</p><p>Please contact us if you need further assistance.</p>',
            'sms_template_detail'    => 'Your nurse request has been cancelled. Reason: [[ description ]]. Please contact us for assistance.',
            'whatsapp_template_detail' => 'Your nurse request has been cancelled. Reason: [[ description ]]. Please contact us for assistance.',
        ]);
        $t->defaultNotificationTemplateMap()->firstOrCreate(['language' => 'en', 'user_type' => 'nurse'], [
            'language'               => 'en',
            'user_type'              => 'nurse',
            'status'                 => 1,
            'subject'                => 'Nurse Request Cancelled by Admin',
            'mail_subject'           => 'Nurse Request Cancelled by Admin',
            'sms_subject'            => 'Request Cancelled',
            'whatsapp_subject'       => 'Request Cancelled',
            'notification_message'   => 'A nurse request assigned to you has been cancelled by admin.',
            'notification_link'      => '',
            'template_detail'        => '<p>A nurse request assigned to you has been cancelled by admin.</p><p>Reason: [[ description ]]</p>',
            'mail_template_detail'   => '<p>A nurse request assigned to you has been cancelled by admin.</p><p>Reason: [[ description ]]</p>',
            'sms_template_detail'    => 'A request assigned to you has been cancelled by admin. Reason: [[ description ]].',
            'whatsapp_template_detail' => 'A request assigned to you has been cancelled by admin. Reason: [[ description ]].',
        ]);

        // Register types in Constants table so they appear in admin dropdowns
        $constants = [
            ['type' => 'notification_type', 'value' => 'new_nurse_request',              'name' => 'New Nurse Request Received'],
            ['type' => 'notification_type', 'value' => 'nurse_assigned',                 'name' => 'Nurse Assigned to Request'],
            ['type' => 'notification_type', 'value' => 'nurse_request_confirmed',        'name' => 'Nurse Request Confirmed'],
            ['type' => 'notification_type', 'value' => 'nurse_request_in_progress',      'name' => 'Nurse Request In Progress'],
            ['type' => 'notification_type', 'value' => 'nurse_request_completed',        'name' => 'Nurse Request Completed'],
            ['type' => 'notification_type', 'value' => 'nurse_request_cancelled',        'name' => 'Nurse Request Cancelled by Patient'],
            ['type' => 'notification_type', 'value' => 'nurse_request_cancelled_by_admin', 'name' => 'Nurse Request Cancelled by Admin'],
        ];

        foreach ($constants as $c) {
            Constant::updateOrCreate(
                ['type' => $c['type'], 'value' => $c['value']],
                $c
            );
        }
    }

    public function down(): void
    {
        NotificationTemplate::whereIn('type', $this->types)->delete();

        Constant::where('type', 'notification_type')
            ->whereIn('value', $this->types)
            ->delete();
    }
};
