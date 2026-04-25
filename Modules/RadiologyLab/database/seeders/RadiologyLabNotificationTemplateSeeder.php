<?php

namespace Modules\RadiologyLab\database\seeders;

use Illuminate\Database\Seeder;
use Modules\NotificationTemplate\Models\NotificationTemplate;

class RadiologyLabNotificationTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'type' => 'new_test_order',
                'name' => 'new_test_order',
                'label' => 'New Test Order',
                'to' => '["admin","user"]',
                'mappings' => [
                    'admin' => [
                        'subject' => 'New Test Order [[ order_number ]]',
                        'notification_message' => 'A new test order [[ order_number ]] has been placed by [[ patient_name ]].',
                        'mail_subject' => 'New Test Order [[ order_number ]]',
                        'mail_template_detail' => '<p>Hello Admin,</p><p>A new test order <strong>[[ order_number ]]</strong> has been placed by <strong>[[ patient_name ]]</strong> on [[ order_date ]].</p><p>Please review and process the order.</p>',
                    ],
                    'user' => [
                        'subject' => 'Test Order Confirmed [[ order_number ]]',
                        'notification_message' => 'Your test order [[ order_number ]] has been placed successfully.',
                        'mail_subject' => 'Your Test Order [[ order_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>Your test order <strong>[[ order_number ]]</strong> has been placed successfully on [[ order_date ]].</p><p>We will notify you once it is confirmed.</p>',
                    ],
                ],
            ],
            [
                'type' => 'test_order_confirmed',
                'name' => 'test_order_confirmed',
                'label' => 'Test Order Confirmed',
                'to' => '["user"]',
                'mappings' => [
                    'user' => [
                        'subject' => 'Test Order Confirmed [[ order_number ]]',
                        'notification_message' => 'Your test order [[ order_number ]] has been confirmed.',
                        'mail_subject' => 'Test Order Confirmed [[ order_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>Your test order <strong>[[ order_number ]]</strong> has been confirmed. Please visit the lab for sample collection.</p>',
                    ],
                ],
            ],
            [
                'type' => 'test_order_technician_assigned',
                'name' => 'test_order_technician_assigned',
                'label' => 'Technician Assigned to Test Order',
                'to' => '["user","lab_technician"]',
                'mappings' => [
                    'user' => [
                        'subject' => 'Technician Assigned [[ order_number ]]',
                        'notification_message' => 'A lab technician has been assigned to your test order [[ order_number ]].',
                        'mail_subject' => 'Technician Assigned [[ order_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>Lab technician <strong>[[ technician_name ]]</strong> has been assigned to your test order <strong>[[ order_number ]]</strong>.</p>',
                    ],
                    'lab_technician' => [
                        'subject' => 'New Assignment: Test Order [[ order_number ]]',
                        'notification_message' => 'You have been assigned to test order [[ order_number ]].',
                        'mail_subject' => 'New Assignment: Test Order [[ order_number ]]',
                        'mail_template_detail' => '<p>Hello [[ technician_name ]],</p><p>You have been assigned to test order <strong>[[ order_number ]]</strong> for patient <strong>[[ patient_name ]]</strong>.</p>',
                    ],
                ],
            ],
            [
                'type' => 'test_order_sample_collected',
                'name' => 'test_order_sample_collected',
                'label' => 'Test Order Sample Collected',
                'to' => '["user"]',
                'mappings' => [
                    'user' => [
                        'subject' => 'Sample Collected [[ order_number ]]',
                        'notification_message' => 'Your sample for test order [[ order_number ]] has been collected.',
                        'mail_subject' => 'Sample Collected [[ order_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>Your sample for test order <strong>[[ order_number ]]</strong> has been collected and is being prepared for processing.</p>',
                    ],
                ],
            ],
            [
                'type' => 'test_order_processing',
                'name' => 'test_order_processing',
                'label' => 'Test Order Processing',
                'to' => '["user"]',
                'mappings' => [
                    'user' => [
                        'subject' => 'Tests Being Processed [[ order_number ]]',
                        'notification_message' => 'Your test order [[ order_number ]] is now being processed.',
                        'mail_subject' => 'Tests Being Processed [[ order_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>Your test order <strong>[[ order_number ]]</strong> is now being processed. Results will be available soon.</p>',
                    ],
                ],
            ],
            [
                'type' => 'test_order_completed',
                'name' => 'test_order_completed',
                'label' => 'Test Order Completed',
                'to' => '["user","doctor"]',
                'mappings' => [
                    'user' => [
                        'subject' => 'Test Results Ready [[ order_number ]]',
                        'notification_message' => 'Results for your test order [[ order_number ]] are now available.',
                        'mail_subject' => 'Test Results Ready [[ order_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>The results for your test order <strong>[[ order_number ]]</strong> are now available. You can view them in your account.</p>',
                    ],
                    'doctor' => [
                        'subject' => 'Test Results Ready [[ order_number ]]',
                        'notification_message' => 'Results for test order [[ order_number ]] (patient: [[ patient_name ]]) are ready.',
                        'mail_subject' => 'Test Results Ready [[ order_number ]]',
                        'mail_template_detail' => '<p>Hello Doctor,</p><p>Results for test order <strong>[[ order_number ]]</strong> (patient: <strong>[[ patient_name ]]</strong>) are now available for review.</p>',
                    ],
                ],
            ],
            [
                'type' => 'test_order_delivered',
                'name' => 'test_order_delivered',
                'label' => 'Test Order Delivered',
                'to' => '["user"]',
                'mappings' => [
                    'user' => [
                        'subject' => 'Report Delivered [[ order_number ]]',
                        'notification_message' => 'Your test report for order [[ order_number ]] has been delivered.',
                        'mail_subject' => 'Report Delivered [[ order_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>The report for your test order <strong>[[ order_number ]]</strong> has been delivered. You can download it from your account.</p>',
                    ],
                ],
            ],
            [
                'type' => 'test_order_cancelled',
                'name' => 'test_order_cancelled',
                'label' => 'Test Order Cancelled by Patient',
                'to' => '["admin"]',
                'mappings' => [
                    'admin' => [
                        'subject' => 'Test Order Cancelled [[ order_number ]]',
                        'notification_message' => 'Test order [[ order_number ]] has been cancelled by patient [[ patient_name ]].',
                        'mail_subject' => 'Test Order Cancelled [[ order_number ]]',
                        'mail_template_detail' => '<p>Hello Admin,</p><p>Test order <strong>[[ order_number ]]</strong> has been cancelled by patient <strong>[[ patient_name ]]</strong>.</p>',
                    ],
                ],
            ],
            [
                'type' => 'test_order_cancelled_by_admin',
                'name' => 'test_order_cancelled_by_admin',
                'label' => 'Test Order Cancelled by Admin',
                'to' => '["user"]',
                'mappings' => [
                    'user' => [
                        'subject' => 'Test Order Cancelled [[ order_number ]]',
                        'notification_message' => 'Your test order [[ order_number ]] has been cancelled.',
                        'mail_subject' => 'Test Order Cancelled [[ order_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>Your test order <strong>[[ order_number ]]</strong> has been cancelled. Please contact us if you have questions.</p>',
                    ],
                ],
            ],
            [
                'type' => 'test_report_ready',
                'name' => 'test_report_ready',
                'label' => 'Test Report Ready',
                'to' => '["user"]',
                'mappings' => [
                    'user' => [
                        'subject' => 'Test Report Available [[ order_number ]]',
                        'notification_message' => 'A report for your test order [[ order_number ]] is now available.',
                        'mail_subject' => 'Test Report Available [[ order_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>A report for your test order <strong>[[ order_number ]]</strong> has been generated and is available for download.</p>',
                    ],
                ],
            ],
            [
                'type' => 'facility_booking_confirmed',
                'name' => 'facility_booking_confirmed',
                'label' => 'Facility Booking Confirmed',
                'to' => '["user"]',
                'mappings' => [
                    'user' => [
                        'subject' => 'Booking Confirmed [[ booking_number ]]',
                        'notification_message' => 'Your booking [[ booking_number ]] at [[ facility_name ]] has been confirmed.',
                        'mail_subject' => 'Booking Confirmed [[ booking_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>Your booking <strong>[[ booking_number ]]</strong> at <strong>[[ facility_name ]]</strong> on [[ booking_date ]] at [[ booking_time ]] has been confirmed.</p>',
                    ],
                ],
            ],
            [
                'type' => 'facility_booking_cancelled',
                'name' => 'facility_booking_cancelled',
                'label' => 'Facility Booking Cancelled',
                'to' => '["user"]',
                'mappings' => [
                    'user' => [
                        'subject' => 'Booking Cancelled [[ booking_number ]]',
                        'notification_message' => 'Your booking [[ booking_number ]] has been cancelled.',
                        'mail_subject' => 'Booking Cancelled [[ booking_number ]]',
                        'mail_template_detail' => '<p>Dear [[ patient_name ]],</p><p>Your booking <strong>[[ booking_number ]]</strong> has been cancelled. Please contact us to reschedule.</p>',
                    ],
                ],
            ],
        ];

        $defaultChannels = [
            'IS_MAIL' => '1',
            'PUSH_NOTIFICATION' => '1',
            'IS_CUSTOM_WEBHOOK' => '0',
            'IS_SMS' => '0',
            'IS_WHATSAPP' => '0',
        ];

        foreach ($templates as $tpl) {
            $existing = NotificationTemplate::where('type', $tpl['type'])->first();
            if ($existing) {
                continue;
            }

            $template = NotificationTemplate::create([
                'type' => $tpl['type'],
                'name' => $tpl['name'],
                'label' => $tpl['label'],
                'status' => 1,
                'to' => $tpl['to'],
                'channels' => $defaultChannels,
            ]);

            foreach ($tpl['mappings'] as $userType => $content) {
                $template->defaultNotificationTemplateMap()->create([
                    'language' => 'en',
                    'notification_link' => '',
                    'notification_message' => $content['notification_message'],
                    'status' => 1,
                    'subject' => $content['subject'],
                    'user_type' => $userType,
                    'template_detail' => $content['mail_template_detail'],
                    'mail_template_detail' => $content['mail_template_detail'],
                    'mail_subject' => $content['mail_subject'],
                    'sms_template_detail' => $content['notification_message'],
                    'sms_subject' => $content['subject'],
                    'whatsapp_template_detail' => $content['notification_message'],
                    'whatsapp_subject' => $content['subject'],
                ]);

                $template->defaultNotificationTemplateMap()->create([
                    'language' => 'ar',
                    'notification_link' => '',
                    'notification_message' => $content['notification_message'],
                    'status' => 1,
                    'subject' => $content['subject'],
                    'user_type' => $userType,
                    'template_detail' => $content['mail_template_detail'],
                    'mail_template_detail' => $content['mail_template_detail'],
                    'mail_subject' => $content['mail_subject'],
                    'sms_template_detail' => $content['notification_message'],
                    'sms_subject' => $content['subject'],
                    'whatsapp_template_detail' => $content['notification_message'],
                    'whatsapp_subject' => $content['subject'],
                ]);
            }
        }
    }
}
