<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Setting;
use App\Models\Customer;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use App\Models\PackageUsagesSummary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\PackageController;

class Demo
{
    public function mailCredential()
    {
        $credentials = array (
            'mail_type' => 'smtp',
            'host_address' => 'smtp.gmail.com',
            'mail_port' => '587',
            'encryption' => 'tls',
            'mail_username' => 'mkraju.doorsoft@gmail.com',
            'mail_password' => 'mhmrpahjwmyyveuc',
            'mail_from' => 'no-reply@salonbuddy.com',
            'mail_from_name' => 'Salon Buddy',
            'mail_api_key' => '',
        );
        return $credentials;
    }

    public function whatsappCredential()
    {
        $credentials = array (
            'whatsapp_type' => 'RC Soft',
            'whatsapp_app_key' => '',
            'whatsapp_auth_key' => '',
            'whatsapp_account_sid' => '',
            'whatsapp_auth_token' => '',
            'whatsapp_from_number' => ''
        );
        return $credentials;
    }

    public function smsCredential()
    {
        $credentials = array (
            'sms_type' => 'mobishastra',
            'sms_api_key' => '',
            'sms_api_secret' => '',
            'sms_sender_id' => '',
            'sms_username' => '',
            'sms_password' => ''
        );
        return $credentials;
    }

}

