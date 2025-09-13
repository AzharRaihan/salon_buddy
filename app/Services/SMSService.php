<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class SMSService
{
    /**
     * Send SMS using configured SMS service
     *
     * @param string $to
     * @param string $message
     * @return array
     */
    public function sendSMS($to, $message)
    {
        try {
            // Get SMS settings from database
            $smsType = Setting::getSetting('sms_type') ? : 'mobishastra';
            $smsApiKey = Setting::getSetting('sms_api_key');
            $smsApiSecret = Setting::getSetting('sms_api_secret');
            $smsSenderId = Setting::getSetting('sms_sender_id');
            $smsUsername = Setting::getSetting('sms_username');
            $smsPassword = Setting::getSetting('sms_password');

            switch ($smsType) {
                case 'mobishastra':
                    return $this->sendViaMobishastra($to, $message, $smsApiKey, $smsSenderId);
                case 'twilio':
                    return $this->sendViaTwilio($to, $message, $smsApiKey, $smsApiSecret, $smsSenderId);
                case 'textlocal':
                    return $this->sendViaTextLocal($to, $message, $smsApiKey, $smsSenderId);
                case 'mimsms':
                    return $this->sendViaMiMSMS($to, $message, $smsUsername, $smsPassword, $smsSenderId);
                default:
                    return [
                        'status' => 'Error',
                        'message' => 'Unsupported SMS service: ' . $smsType
                    ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'Error',
                'message' => 'SMS sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via Mobishastra
     *
     * @param string $to
     * @param string $message
     * @param string $apiKey
     * @param string $senderId
     * @return array
     */
    private function sendViaMobishastra($to, $message, $apiKey, $senderId)
    {
        try {
            $response = Http::post('https://api.mobishastra.com/send_message.php', [
                'apikey' => $apiKey,
                'sender' => $senderId,
                'number' => $to,
                'message' => $message,
                'type' => 'text'
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] === 'success') {
                return [
                    'status' => 'Success',
                    'message' => 'SMS sent successfully via Mobishastra'
                ];
            } else {
                return [
                    'status' => 'Error',
                    'message' => 'Mobishastra SMS sending failed: ' . ($result['message'] ?? 'Unknown error')
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'Error',
                'message' => 'Mobishastra SMS sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via Twilio
     *
     * @param string $to
     * @param string $message
     * @param string $accountSid
     * @param string $authToken
     * @param string $fromNumber
     * @return array
     */
    private function sendViaTwilio($to, $message, $accountSid, $authToken, $fromNumber)
    {
        try {
            $response = Http::withBasicAuth($accountSid, $authToken)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'To' => $to,
                    'From' => $fromNumber,
                    'Body' => $message
                ]);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && in_array($result['status'], ['queued', 'sent', 'delivered'])) {
                return [
                    'status' => 'Success',
                    'message' => 'SMS sent successfully via Twilio'
                ];
            } else {
                return [
                    'status' => 'Error',
                    'message' => 'Twilio SMS sending failed: ' . ($result['message'] ?? 'Unknown error')
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'Error',
                'message' => 'Twilio SMS sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via TextLocal
     *
     * @param string $to
     * @param string $message
     * @param string $apiKey
     * @param string $senderId
     * @return array
     */
    private function sendViaTextLocal($to, $message, $apiKey, $senderId)
    {
        try {
            $response = Http::post('https://api.textlocal.in/send/', [
                'apikey' => $apiKey,
                'sender' => $senderId,
                'numbers' => $to,
                'message' => $message,
                'test' => false
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] === 'success') {
                return [
                    'status' => 'Success',
                    'message' => 'SMS sent successfully via TextLocal'
                ];
            } else {
                return [
                    'status' => 'Error',
                    'message' => 'TextLocal SMS sending failed: ' . ($result['message'] ?? 'Unknown error')
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'Error',
                'message' => 'TextLocal SMS sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via MiMSMS
     *
     * @param string $to
     * @param string $message
     * @param string $username
     * @param string $password
     * @param string $senderId
     * @return array
     */
    private function sendViaMiMSMS($to, $message, $username, $password, $senderId)
    {
        try {
            $response = Http::post('https://api.mimsms.com/smsapi', [
                'username' => $username,
                'password' => $password,
                'sender' => $senderId,
                'number' => $to,
                'message' => $message,
                'type' => 'text'
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && $result['status'] === 'success') {
                return [
                    'status' => 'Success',
                    'message' => 'SMS sent successfully via MiMSMS'
                ];
            } else {
                return [
                    'status' => 'Error',
                    'message' => 'MiMSMS SMS sending failed: ' . ($result['message'] ?? 'Unknown error')
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'Error',
                'message' => 'MiMSMS SMS sending failed: ' . $e->getMessage()
            ];
        }
    }
} 