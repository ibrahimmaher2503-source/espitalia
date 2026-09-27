<?php

namespace App\Notifications;

use App\Broadcasting\CustomWebhook;
use App\Broadcasting\OneSingleChannel;
use App\Mail\MailMailableSend;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\NotificationTemplate\Models\NotificationTemplate;
use Modules\NotificationTemplate\Models\NotificationTemplateContentMapping;
use Spatie\WebhookServer\WebhookCall;
use App\Broadcasting\FcmChannel;
use Benwilkins\FCM\Message;
use Google\Client as Google_Client;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use Twilio\Rest\Client;
use App\Models\User;

class CommonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $type;

    public $data;

    public $subject;

    public $notification;

    public $notification_message;

    public $notification_link;

    public $appData;

    public $custom_webhook;

    /**
     * Create a new notification instance.
     */
    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;

        $userType = $data['user_type'];
        $notifications = NotificationTemplate::where('type', $this->type)
            ->with('defaultNotificationTemplateMap')
            ->first();

        $notify_data = NotificationTemplateContentMapping::where('template_id', $notifications->id)->get();
        $templateData = $notify_data->where('user_type', $userType)->first();
        $this->template_data = $templateData;

        $this->notification = NotificationTemplate::where('type', $this->type)->with('defaultNotificationTemplateMap')->first();
        $this->subject = $this->notification->defaultNotificationTemplateMap->subject;
        $this->type = ucwords(str_replace('_', ' ', $this->notification->type)) . '!';
        $this->notification_message = $this->notification->defaultNotificationTemplateMap->notification_message;
        $this->data['notification_message'] = $this->notification_message;
        $this->notification_link = $this->notification->defaultNotificationTemplateMap->notification_link;
        // foreach ($this->data as $key => $value) {
        //     $this->subject = str_replace('[[ ' . $key . ' ]]', $this->data[$key], $this->subject);
        //     $this->notification_message = str_replace('[[ ' . $key . ' ]]', $this->data[$key], $this->notification_message);
        //     $this->notification_link = str_replace('[[ ' . $key . ' ]]', $this->data[$key], $this->notification_link);
        // }
        $this->subject = $this->subject != '' ? $this->subject : 'None';
        $this->notification_message = $this->notification_message != '' ? $this->notification_message : __('messages.default_notification_body');

        $this->appData = $this->notification->channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $notificationData = $this->data;
        $templateData = $this->template_data;

        $notificationSettings = $this->appData;
        $notification_settings = [];
        $notification_access = isset($notificationSettings[$this->type]) ? $notificationSettings[$this->type] : [];




        if (isset($notificationSettings)) {
            foreach ($notificationSettings as $key => $notification) {
                if ($notification) {
                    switch ($key) {

                        case 'PUSH_NOTIFICATION':
                            array_push($notification_settings, FcmChannel::class);

                            break;

                        case 'IS_CUSTOM_WEBHOOK':
                            array_push($notification_settings, CustomWebhook::class);

                            break;

                        case 'IS_MAIL':
                            array_push($notification_settings, 'mail');

                            break;
                        case 'IS_SMS':
                            $templateDetail = $templateData->sms_template_detail ?? null;
                            foreach ($notificationData as $key => $value) {
                                $templateDetail = str_replace('[[ ' . $key . ' ]]', $notificationData[$key], $templateDetail);
                            }

                            $notificationData['type'] = $templateData->sms_subject ?? 'None';
                            $notificationData['message'] = $templateDetail ?? __('messages.default_notification_body');

                            $this->sendSmsMessage($notificationData);

                            break;
                        case 'IS_WHATSAPP':
                            $templateDetail = $templateData->whatsapp_template_detail ?? null;
                            foreach ($notificationData as $key => $value) {
                                $templateDetail = str_replace('[[ ' . $key . ' ]]', $notificationData[$key], $templateDetail);
                            }

                            $notificationData['type'] = $templateData->whatsapp_subject ?? 'None';
                            $notificationData['message'] = $templateDetail ?? __('messages.default_notification_body');

                            $this->sendWhatsAppMessage($notificationData);

                            break;
                    }
                }
            }
        }

        return array_merge($notification_settings, ['database']);
    }

    public function toOneSignal($notifiable)
    {
        $msg = $this->subject;
        if (!isset($msg) && $msg == '') {
            $msg = __('message.notification_body');
        }
        $type = 'booking';
        if (isset($this->data['type']) && $this->data['type'] !== '') {
            $type = $this->data['type'];
        }
        $heading = $this->subject;

        return onesingle([
            'app_id' => setting('onesignal_app_id'),
            'include_player_ids' => [$notifiable->player_id],
            'data' => [
                'type' => $this->subject,
                'additional_data' => $this->data,
            ],
            'headings' => [
                'en' => $heading,
            ],
            'contents' => [
                'en' => $msg,
            ],
        ]);
    }

    /**
     * Get mail notification
     *
     * @param  mixed  $notifiable
     * @return MailMailableSend
     */
    public function toMail($notifiable)
    {
        $email = '';

        if (isset($notifiable->email)) {
            $email = $notifiable->email;
        } else {
            $email = $notifiable->routes['mail'];
        }

        return (new MailMailableSend($this->notification, $this->data, $this->type))->to($email)
            ->bcc(isset($this->notification->bcc) ? json_decode($this->notification->bcc) : [])
            ->cc(isset($this->notification->cc) ? json_decode($this->notification->cc) : [])
            ->subject($this->subject);
    }

    public function toWebhook($notifiable)
    {
        $key = setting('custom_webhook_content_key');
        $url = setting('custom_webhook_url');
        $secrate_key = setting('app_name');
        $msg = 'Subject: ' . $this->subject . "\nDescription: " . strip_tags($this->notification_message) . "\n" . 'Link: ' . $this->notification_link;

        return WebhookCall::create()
            ->url($url)
            ->payload([$key => $msg])
            ->useSecret($secrate_key)
            ->dispatch();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'subject' => $this->subject,
            'data' => $this->data,
        ];
    }


    public function toFcm($notifiable)
    {
        $type = (string) ($this->data['type'] ?? 'booking');
        $heading = $this->type;
        // Never put names, free-form medical text, amounts, or template data
        // into a lock-screen notification. The authenticated notification
        // center remains the source for full details.
        $safeData = self::safeFcmData($this->data);
        $additionalData = json_encode($safeData, JSON_THROW_ON_ERROR);

        return $this->fcm([
            "message" => [
                "topic" => 'user_' . $notifiable->id,
                "notification" => [
                    "title" => $heading,
                    "body" => "You have a new notification. Open Espitalia to view details.",
                ],
                "data" => [
                    "sound" => "default",
                    "type" => $type,
                    "additional_data" => $additionalData,
                ],
                "android" => [
                    "priority" => "high",
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "category" => "NEW_MESSAGE_CATEGORY",
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Keep only non-sensitive routing fields in FCM data payloads.
     */
    public static function safeFcmData(array $data): array
    {
        $allowed = [
            'type',
            'id',
            'request_id',
            'admission_request_id',
            'order_id',
            'test_order_id',
            'encounter_id',
            'patient_encounter_id',
            'reference_number',
            'visit_reference',
            'request_reference',
        ];

        $safe = [];
        foreach ($allowed as $key) {
            if (!array_key_exists($key, $data) || is_array($data[$key]) || is_object($data[$key])) {
                continue;
            }
            $value = trim((string) $data[$key]);
            if ($value !== '') {
                $safe[$key] = $value;
            }
        }

        return $safe;
    }

    function fcm($fields)
    {
        $projectID = (string) config('services.firebase.project_id');
        if ($projectID === '' || ! preg_match('/^[a-z0-9-]+$/', $projectID)) {
            Log::warning('FCM delivery skipped because FIREBASE_PROJECT_ID is not configured.');
            return false;
        }

        $access_token = $this->getAccessToken();
        if ($access_token === null || $access_token === '') {
            Log::warning('FCM delivery skipped because Firebase credentials are not configured.');
            return false;
        }

        $headers = [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ];
        $ch = curl_init('https://fcm.googleapis.com/v1/projects/' . $projectID . '/messages:send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            Log::warning('FCM delivery failed at the transport layer.', [
                'http_status' => $httpCode,
            ]);
        }

        curl_close($ch);
        return $response !== false && $httpCode >= 200 && $httpCode < 300;
    }
    function getAccessToken()
    {
        $credentialsPath = config('services.firebase.credentials');
        if (! is_string($credentialsPath) || $credentialsPath === '' || ! is_file($credentialsPath)) {
            return null;
        }

        try {
            $client = new Google_Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

            $token = $client->fetchAccessTokenWithAssertion();
            return is_array($token) ? ($token['access_token'] ?? null) : null;
        } catch (\Throwable $exception) {
            Log::warning('FCM access token could not be created.', [
                'error_type' => get_class($exception),
            ]);
            return null;
        }
    }


    function sendSmsMessage($data)
    {
        $settings = Setting::where('type', 'is_sms_integration')
            ->whereIn('name', ['sms_account_sid', 'sms_auth_token', 'mobile_number'])
            ->pluck('val', 'name');

        $user = User::where('id', $data['person_id'])->first();

        if (empty($settings['sms_account_sid']) || empty($settings['sms_auth_token']) || empty($settings['mobile_number'])) {
            return false;
        }

        if (empty($data['person_id']) || empty($data['message'])) {
            return false;
        }

        $sid = $settings['sms_account_sid'];
        $authToken = $settings['sms_auth_token'];
        $twilioPhoneNumber = $settings['mobile_number'];
        $recipientNumber = $user->mobile;
        $messageBody = strip_tags($data['message']);

        $client = new Client($sid, $authToken);

        try {
            $message = $client->messages->create(
                $recipientNumber,
                [
                    'from' => $twilioPhoneNumber,
                    'body' => $messageBody
                ]
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    function sendWhatsAppMessage($data)
    {
        $settings = Setting::where('type', 'is_whatsapp_integration')
            ->whereIn('name', ['whatsapp_account_sid', 'whatsapp_auth_token', 'whatsapp_number'])
            ->pluck('val', 'name');

        $user = User::where('id', $data['person_id'])->first();

        if (empty($settings['whatsapp_account_sid']) || empty($settings['whatsapp_auth_token']) || empty($settings['whatsapp_number'])) {
            return false;
        }

        if (empty($user) || empty($user->mobile) || empty($data['message'])) {
            return false;
        }

        $sid = $settings['whatsapp_account_sid'];
        $authToken = $settings['whatsapp_auth_token'];
        $twilioWhatsAppNumber = 'whatsapp:' . $settings['whatsapp_number'];
        $recipientNumber = 'whatsapp:' . $user->mobile;
        $messageBody = strip_tags($data['message']);
        $client = new Client($sid, $authToken);

        try {
            $client->messages->create(
                $recipientNumber,
                [
                    'from' => $twilioWhatsAppNumber,
                    'body' => $messageBody
                ]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
