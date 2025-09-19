<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Demo;

class WhatsappService
{
    /**
     * Send WhatsApp message using configured WhatsApp service
     *
     * @param string $to
     * @param string $message
     * @return array
     */
    public function sendWhatsapp($to, $message)
    {
        try {

            // Get WhatsApp settings from database
            if(demoCheck()) {
                $demo = new Demo();
                $credentials = $demo->whatsappCredential();
                $whatsappType = $credentials['whatsapp_type'];
                $whatsappAppKey = $credentials['whatsapp_app_key'];
                $whatsappAuthKey = $credentials['whatsapp_auth_key'];
                $whatsappAccountSid = $credentials['whatsapp_account_sid'];
                $whatsappAuthToken = $credentials['whatsapp_auth_token'];
                $whatsappFromNumber = $credentials['whatsapp_from_number'];
            }else {
                $whatsappType = Setting::getSetting('whatsapp_type') ?: 'RC Soft';
                $whatsappAppKey = Setting::getSetting('whatsapp_app_key');
                $whatsappAuthKey = Setting::getSetting('whatsapp_auth_key');
                $whatsappAccountSid = Setting::getSetting('whatsapp_account_sid');
                $whatsappAuthToken = Setting::getSetting('whatsapp_auth_token');
                $whatsappFromNumber = Setting::getSetting('whatsapp_from_number');
            }

            switch ($whatsappType) {
                case 'RC Soft':
                    return $this->sendViaRCSoft($to, $message, $whatsappAppKey, $whatsappAuthKey);
                case 'Twilio':
                    return $this->sendViaTwilio($to, $message, $whatsappAccountSid, $whatsappAuthToken, $whatsappFromNumber);
                default:
                    return [
                        'status' => 'Error',
                        'message' => 'Unsupported WhatsApp service: ' . $whatsappType
                    ];
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp sending failed', ['exception' => $e]);
            return [
                'status' => 'Error',
                'message' => 'WhatsApp sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp message via RC Soft
     *
     * @param string $to
     * @param string $message
     * @param string $appKey
     * @param string $authKey
     * @return array
     */
    private function sendViaRCSoft($to, $message, $appKey, $authKey)
    {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://whats-api.rcsoft.in/api/create-message',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'appkey' => $appKey,
                    'authkey' => $authKey,
                    'to' => $to,
                    'message' => $message,
                    'sandbox' => 'false'
                ),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded',
                ),
            ));

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error) {
                Log::error('RC Soft WhatsApp cURL error', ['error' => $error]);
                return [
                    'status' => 'Error',
                    'message' => 'RC Soft WhatsApp cURL error: ' . $error
                ];
            }

            $result = json_decode($response, true);

            if ($httpCode === 200 && isset($result['status']) && $result['status'] === 'success') {
                Log::info('RC Soft WhatsApp sent successfully', ['to' => $to]);
                return [
                    'status' => 'Success',
                    'message' => 'WhatsApp message sent successfully via RC Soft'
                ];
            } else {
                Log::error('RC Soft WhatsApp API error', [
                    'http_code' => $httpCode,
                    'response' => $result
                ]);
                return [
                    'status' => 'Error',
                    'message' => 'RC Soft WhatsApp sending failed: ' . ($result['message'] ?? 'Unknown error')
                ];
            }
        } catch (\Exception $e) {
            Log::error('RC Soft WhatsApp exception', ['exception' => $e]);
            return [
                'status' => 'Error',
                'message' => 'RC Soft WhatsApp sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp message via Twilio
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
            // Ensure phone numbers are in proper format
            $to = $this->formatPhoneNumber($to);
            $fromNumber = $this->formatPhoneNumber($fromNumber);

            // Use WhatsApp Business API endpoint
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
            
            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post($url, [
                    'To' => "whatsapp:{$to}",
                    'From' => "whatsapp:{$fromNumber}",
                    'Body' => $message
                ]);

            $result = $response->json();

            if ($response->successful() && isset($result['status']) && in_array($result['status'], ['queued', 'sent', 'delivered'])) {
                Log::info('Twilio WhatsApp sent successfully', ['to' => $to]);
                return [
                    'status' => 'Success',
                    'message' => 'WhatsApp message sent successfully via Twilio'
                ];
            } else {
                Log::error('Twilio WhatsApp API error', [
                    'status_code' => $response->status(),
                    'response' => $result
                ]);
                return [
                    'status' => 'Error',
                    'message' => 'Twilio WhatsApp sending failed: ' . ($result['message'] ?? 'Unknown error')
                ];
            }
        } catch (\Exception $e) {
            Log::error('Twilio WhatsApp exception', ['exception' => $e]);
            return [
                'status' => 'Error',
                'message' => 'Twilio WhatsApp sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to international format
     *
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Remove all non-digit characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If number doesn't start with country code, assume it's a local number
        // You might want to adjust this based on your default country code
        if (strlen($phoneNumber) === 10) {
            $phoneNumber = '91' . $phoneNumber; // Default to India (+91)
        }
        
        // Add + prefix
        return '+' . $phoneNumber;
    }

    /**
     * Validate WhatsApp settings
     *
     * @param string $provider
     * @return array
     */
    public function validateSettings($provider)
    {
        $errors = [];

        switch ($provider) {
            case 'RC Soft':
                if (!Setting::getSetting('whatsapp_app_key')) {
                    $errors[] = 'RC Soft App Key is required';
                }
                if (!Setting::getSetting('whatsapp_auth_key')) {
                    $errors[] = 'RC Soft Auth Key is required';
                }
                break;
            case 'Twilio':
                if (!Setting::getSetting('whatsapp_account_sid')) {
                    $errors[] = 'Twilio Account SID is required';
                }
                if (!Setting::getSetting('whatsapp_auth_token')) {
                    $errors[] = 'Twilio Auth Token is required';
                }
                if (!Setting::getSetting('whatsapp_from_number')) {
                    $errors[] = 'Twilio From Phone Number is required';
                }
                break;
            default:
                $errors[] = 'Invalid WhatsApp provider';
        }

        return $errors;
    }
} 