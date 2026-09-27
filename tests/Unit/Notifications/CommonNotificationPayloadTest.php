<?php

namespace Tests\Unit\Notifications;

use App\Notifications\CommonNotification;
use Tests\TestCase;

class CommonNotificationPayloadTest extends TestCase
{
    public function test_fcm_data_contains_only_safe_routing_fields(): void
    {
        $safe = CommonNotification::safeFcmData([
            'type' => 'appointment_status_changed',
            'id' => 42,
            'doctor_name' => 'Sensitive Name',
            'patient_name' => 'Sensitive Patient',
            'notification_msg' => 'Diagnosis: confidential',
            'refund_amount' => '1200',
            'notes' => 'Free-form medical text',
            'additional_data' => ['secret' => true],
        ]);

        $this->assertSame([
            'type' => 'appointment_status_changed',
            'id' => '42',
        ], $safe);
    }

    public function test_fcm_uses_the_default_launcher_intent(): void
    {
        $notification = new class extends CommonNotification
        {
            public array $fields = [];

            public function __construct() {}

            public function fcm($fields)
            {
                $this->fields = $fields;
                return true;
            }
        };
        $notification->type = 'Espitalia QA';
        $notification->data = ['type' => 'qa_notification'];

        $this->assertTrue($notification->toFcm((object) ['id' => 2]));
        $this->assertArrayNotHasKey('click_action', $notification->fields['message']['data']);
        $this->assertArrayNotHasKey('notification', $notification->fields['message']['android']);
    }
}
