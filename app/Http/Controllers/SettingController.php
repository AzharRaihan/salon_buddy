<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Setting;
use App\Traits\ApiResponse;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    use ApiResponse, FileUploadTrait;

    /**
     * Get company info
     *
     * @return JsonResponse
     */
    public function companyInfo()
    {
        $company = Company::first();
        return $this->successResponse($company, 'Company info fetched successfully');
    }

    /**
     * Update company info
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function companyInfoPost(Request $request)
    {
        
        $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|max:255',
            'phone'              => 'required|string|max:255',
            'website'            => 'nullable|string|max:255',
            'address'            => 'nullable|string|max:255',
            'currency'           => 'required|string|max:255',
            'currency_position'  => 'required|string|max:255',
            'precision'          => 'required|string|max:255',
            'thousand_separator' => 'required|string|max:255',
            'decimal_separator'  => 'required|string|max:255',
            'date_format'        => 'required|string|max:255',
            'timezone'           => 'required|string|max:255',
            'print_formate'     => 'required|string|max:255',
            'over_sale'          => 'required|string|max:255',
        ]);

        $company = Company::firstOrNew([]);
        if ($request->hasFile('logo')) {
            Log::info($request->file('logo'));
            $company->logo = $this->imageUpload($request->file('logo'), $company->logo, 'company');
        }

        $company->fill($request->except('logo'));
        $company->save();

        return $this->successResponse($company, 'Company info updated successfully');
    }


    /**
     * Update tax settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function taxSettings(Request $request)
    {
        $company = Company::first();
        return $this->successResponse($company, 'Tax settings fetched successfully');
    }
    /**
     * Update tax settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function taxSettingsPost(Request $request)
    {
        $rules = [
            'collect_tax' => 'required|in:Yes,No',
        ];

        if ($request->collect_tax === 'Yes') {
            $rules = array_merge($rules, [
                'tax_type' => 'required|string|max:255', 
                'tax_title' => 'required|string|max:255',
                'tax_registration_no' => 'required|string|max:255',
                'tax_is_gst' => 'required|in:Yes,No',
                'tax_rates.*.name' => 'required|string|max:255',
                'tax_rates.*.percentage' => 'required|numeric|min:0|max:100'
            ]);

            if ($request->tax_is_gst === 'Yes') {
                $rules = array_merge($rules, [
                    'tax_rates.*.name' => 'required|string|in:CGST,IGST,SGST'
                ]);
            }
        }

        $request->validate($rules);

        $company = Company::firstOrNew([]);
        $company->collect_tax = $request->collect_tax;

        if ($request->collect_tax === 'Yes') {
            $company->tax_type = $request->tax_type;
            $company->tax_title = $request->tax_title;
            $company->tax_registration_no = $request->tax_registration_no;
            $company->tax_is_gst = $request->tax_is_gst;
            // Transform tax rates to required format
            $formattedTaxRates = collect($request->tax_rates)->map(function($rate) {
                return [
                    'id' => '1',
                    'tax' => $rate['name'],
                    'tax_rate' => $rate['percentage']
                ];
            })->toArray();
            $company->tax_setting = json_encode($formattedTaxRates);
            // Create tax string from tax names
            $taxNames = collect($request->tax_rates)->pluck('name')->implode(':');
            $company->tax_string = $taxNames . ':';

        } else {
            $company->tax_type = null;
            $company->tax_title = null;
            $company->tax_registration_no = null;
            $company->tax_is_gst = 'No';
            $company->tax_setting = null;
            $company->tax_string = null;
        }

        $company->save();

        return $this->successResponse($company, 'Tax settings updated successfully');
    }

    /**
     * Get white label settings
     *
     * @return JsonResponse
     */
    public function whiteLabel()
    {
        $settings = [
            'site_title'      => Setting::getSetting('site_title'),
            'footer'          => Setting::getSetting('footer'),
            'company_name'    => Setting::getSetting('company_name'),
            'company_website' => Setting::getSetting('company_website'),
            'favicon'         => Setting::getSetting('favicon'),
            'logo'            => Setting::getSetting('logo'),
            'logo_url'        => asset('assets/images/logo/' . Setting::getSetting('logo')),
            'favicon_url'     => asset('assets/images/favicon/' . Setting::getSetting('favicon')),
        ];

        return $this->successResponse($settings, 'White label settings fetched successfully');
    }

    /**
     * Update white label settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function whiteLabelPost(Request $request)
    {
        $request->validate([
            'site_title'      => 'required|string|max:255',
            'footer'          => 'required|string|max:255',
            'company_name'    => 'required|string|max:255',
            'company_website' => 'required|string|max:255',
        ]);

        // Handle file uploads
        if ($request->hasFile('favicon')) {
            $favicon = $this->imageUpload($request->file('favicon'), Setting::getSetting('favicon'), 'favicon');
            Setting::setSetting('favicon', basename($favicon));
        }

        if ($request->hasFile('logo')) {
            $logo = $this->imageUpload($request->file('logo'), Setting::getSetting('logo'), 'logo');
            Setting::setSetting('logo', basename($logo));
        }

        // Save other settings
        Setting::setSetting('site_title', $request->site_title);
        Setting::setSetting('footer', $request->footer);
        Setting::setSetting('company_name', $request->company_name);
        Setting::setSetting('company_website', $request->company_website);

        return $this->successResponse(null, 'White label settings updated successfully');
    }

    /**
     * Get mail settings
     *
     * @return JsonResponse
     */
    public function mailSettings()
    {
        $settings = [
            'mail_type'      => Setting::getSetting('mail_type'),
            'host_address'   => Setting::getSetting('host_address'),
            'mail_port'      => Setting::getSetting('mail_port'),
            'encryption'     => Setting::getSetting('encryption'),
            'mail_username'  => Setting::getSetting('mail_username'),
            'mail_password'  => Setting::getSetting('mail_password'),
            'mail_from'      => Setting::getSetting('mail_from'),
            'mail_from_name' => Setting::getSetting('mail_from_name'),
            'mail_api_key'   => Setting::getSetting('mail_api_key'),
        ];

        return $this->successResponse($settings, 'Mail settings fetched successfully');
    }

    /**
     * Update mail settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function mailSettingsPost(Request $request)
    {
        $request->validate([
            'mail_type'      => 'required|string|in:SMTP,Mailgun,Sendinblue',
            'host_address'   => 'required|string|max:255',
            'mail_port'      => 'required|string|max:255',
            'encryption'     => 'required|string|in:tls,ssl',
            'mail_username'  => 'required|string|max:255',
            'mail_password'  => 'required|string|max:255',
            'mail_from'      => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'mail_api_key'   => 'required_if:mail_type,Mailgun,Sendinblue|max:255',
        ]);

        // Save mail settings
        Setting::setSetting('mail_type', $request->mail_type);
        Setting::setSetting('host_address', $request->host_address);
        Setting::setSetting('mail_port', $request->mail_port);
        Setting::setSetting('encryption', $request->encryption);
        Setting::setSetting('mail_username', $request->mail_username);
        Setting::setSetting('mail_password', $request->mail_password);
        Setting::setSetting('mail_from', $request->mail_from);
        Setting::setSetting('mail_from_name', $request->mail_from_name);
        Setting::setSetting('mail_api_key', $request->mail_api_key);

        return $this->successResponse(null, 'Mail settings updated successfully');
    }

    /**
     * Get payment settings
     *
     * @return JsonResponse
     */
    public function paymentSettings()
    {
        $settings = [
            'paypal_enabled'       => Setting::getSetting('paypal_enabled', false),
            'paypal_mode'          => Setting::getSetting('paypal_mode', 'sandbox'),
            'paypal_client_id'     => Setting::getSetting('paypal_client_id'),
            'paypal_client_secret' => Setting::getSetting('paypal_client_secret'),
            'stripe_enabled'       => Setting::getSetting('stripe_enabled', false),
            'stripe_mode'          => Setting::getSetting('stripe_mode', 'sandbox'),
            'stripe_key'           => Setting::getSetting('stripe_key'),
            'stripe_secret'        => Setting::getSetting('stripe_secret'),
            'razorpay_enabled'     => Setting::getSetting('razorpay_enabled', false),
            'razorpay_key'         => Setting::getSetting('razorpay_key'),
            'razorpay_secret'      => Setting::getSetting('razorpay_secret'),
            'paytm_enabled'        => Setting::getSetting('paytm_enabled', false),
            'paytm_key'            => Setting::getSetting('paytm_key'),
            'paytm_secret'         => Setting::getSetting('paytm_secret'),
            'paystack_enabled'     => Setting::getSetting('paystack_enabled', false),
            'paystack_key'         => Setting::getSetting('paystack_key'),
        ];

        return $this->successResponse($settings, 'Payment settings fetched successfully');
    }

    /**
     * Update payment settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentSettingsPost(Request $request)
    {
        $request->validate([
            'paypal_enabled'       => 'required|boolean',
            'paypal_mode'          => 'required|string|in:sandbox,live',
            'paypal_client_id'     => 'required_if:paypal_enabled,true|string|max:255',
            'paypal_client_secret' => 'required_if:paypal_enabled,true|string|max:255',
            'stripe_enabled'       => 'required|boolean',
            'stripe_mode'          => 'required|string|in:sandbox,live',
            'stripe_key'           => 'required_if:stripe_enabled,true|string|max:255',
            'stripe_secret'        => 'required_if:stripe_enabled,true|string|max:255',
            'razorpay_enabled'     => 'required|boolean',
            'razorpay_key'         => 'required_if:razorpay_enabled,true|string|max:255',
            'razorpay_secret'      => 'required_if:razorpay_enabled,true|string|max:255',
            'paytm_enabled'        => 'required|boolean',
            'paytm_key'            => 'required_if:paytm_enabled,true|string|max:255',
            'paytm_secret'         => 'required_if:paytm_enabled,true|string|max:255',
            'paystack_enabled'     => 'required|boolean',
            'paystack_key'         => 'required_if:paystack_enabled,true|string|max:255',
        ]);

        // Save payment settings
        Setting::setSetting('paypal_enabled', $request->paypal_enabled);
        Setting::setSetting('paypal_mode', $request->paypal_mode);
        Setting::setSetting('paypal_client_id', $request->paypal_client_id);
        Setting::setSetting('paypal_client_secret', $request->paypal_client_secret);
        Setting::setSetting('stripe_enabled', $request->stripe_enabled);
        Setting::setSetting('stripe_mode', $request->stripe_mode);
        Setting::setSetting('stripe_key', $request->stripe_key);
        Setting::setSetting('stripe_secret', $request->stripe_secret);
        Setting::setSetting('razorpay_enabled', $request->razorpay_enabled);
        Setting::setSetting('razorpay_key', $request->razorpay_key);
        Setting::setSetting('razorpay_secret', $request->razorpay_secret);
        Setting::setSetting('paytm_enabled', $request->paytm_enabled);
        Setting::setSetting('paytm_key', $request->paytm_key);
        Setting::setSetting('paytm_secret', $request->paytm_secret);
        Setting::setSetting('paystack_enabled', $request->paystack_enabled);
        Setting::setSetting('paystack_key', $request->paystack_key);
        return $this->successResponse(null, 'Payment settings updated successfully');
    }

    /**
     * Get SMS settings
     *
     * @return JsonResponse
     */
    public function smsSettings()
    {
        $settings = [
            'sms_type'        => Setting::getSetting('sms_type', 'mobishastra'),
            'sms_api_key'     => Setting::getSetting('sms_api_key'),
            'sms_api_secret'  => Setting::getSetting('sms_api_secret'),
            'sms_sender_id'   => Setting::getSetting('sms_sender_id'),
            'sms_username'    => Setting::getSetting('sms_username'),
            'sms_password'    => Setting::getSetting('sms_password'),
        ];

        return $this->successResponse($settings, 'SMS settings fetched successfully');
    }

    /**
     * Update SMS settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function smsSettingsPost(Request $request)
    {
        if($request->sms_type == 'mobishastra'){
            $request->validate([
                'sms_api_key'     => 'required|string|max:255',
                'sms_sender_id'   => 'required|string|max:255',
            ]);
        }else if($request->sms_type == 'twilio'){
            $request->validate([
                'sms_api_key'     => 'required|string|max:255',
                'sms_api_secret'  => 'required|string|max:255',
                'sms_sender_id'   => 'required|string|max:255',
            ]);
        }else if($request->sms_type == 'textlocal'){
            $request->validate([
                'sms_api_key'     => 'required|string|max:255',
                'sms_sender_id'   => 'required|string|max:255',
            ]);
        }else if($request->sms_type == 'mimsms'){
            $request->validate([
                'sms_api_key'     => 'required|string|max:255',
                'sms_sender_id'   => 'required|string|max:255',
                'sms_username'    => 'required|string|max:255',
                'sms_password'    => 'required|string|max:255',
            ]);
        }


        // Save SMS settings
        Setting::setSetting('sms_type', $request->sms_type);
        Setting::setSetting('sms_api_key', $request->sms_api_key);
        Setting::setSetting('sms_api_secret', $request->sms_api_secret);
        Setting::setSetting('sms_sender_id', $request->sms_sender_id);
        Setting::setSetting('sms_username', $request->sms_username);
        Setting::setSetting('sms_password', $request->sms_password);

        return $this->successResponse(null, 'SMS settings updated successfully');
    }

    /**
     * Test email functionality
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            $emailService = new \App\Services\EmailService();
            $result = $emailService->sendEmail(
                $request->to,
                $request->subject,
                $request->message
            );

            if ($result['status'] === 'Success') {
                return $this->successResponse(null, 'Test email sent successfully');
            } else {
                return $this->errorResponse('Test email failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Test email failed: ' . $e->getMessage());
        }
    }

    /**
     * Test SMS functionality
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function testSms(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $smsService = new \App\Services\SMSService();
            $result = $smsService->sendSMS($request->to, $request->message);

            if ($result['status'] === 'Success') {
                return $this->successResponse(null, 'Test SMS sent successfully');
            } else {
                return $this->errorResponse('Test SMS failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Test SMS failed: ' . $e->getMessage());
        }
    }

    /**
     * Get all settings
     *
     * @return JsonResponse
     */
    public function allSettings()
    {
        $settings      = Setting::all();
        $settingsArray = [];
        foreach ($settings as $setting) {
            $settingsArray[$setting->key] = $setting->value;
        }

        $settingsArray['logo_url']    = logo_url();
        $settingsArray['favicon_url'] = favicon_url();
        return $this->successResponse($settingsArray, 'All settings fetched successfully');
    }
}
