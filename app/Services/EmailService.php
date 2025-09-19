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
use App\Services\Demo;
class EmailService
{
    /**
     * Send email using configured mail service
     *
     * @param string $to
     * @param string $subject
     * @param int $details_id
     * @param string $type
     * @return array
     */
    public function sendEmail($to, $subject, $details_id, $type, $other_arr = [])
    {
        try {
            // Get entity details for email template
            if ($type == 'Booking') {   
                $result = Booking::with(['customer', 'branch'])->find($details_id);
            } else if ($type == 'Sale') {
                $result = Sale::with(['customer', 'branch'])->find($details_id);
            } else if ($type == 'Package Usage') {
                $package = new PackageController();
                $result = PackageUsagesSummary::with(['customer', 'branch'])->find($details_id);
                $package_summary = $package->show($result->sale_id);
                $result->package_info = $package_summary->getData(true);
            } else if ($type == 'Forgot Password') {
                $result = Customer::find($details_id);
            }
            $result->company = Company::find($result->company_id);
            if (!$result) {
                return [
                    'status' => 'Error',
                    'message' => 'Details not found'
                ];
            }
            
            // Get mail settings from database
            if(demoCheck()) {
                $demo = new Demo();
                $credentials = $demo->mailCredential();
                $mailType = $credentials['mail_type'];
                $hostAddress = $credentials['host_address'];
                $mailPort = $credentials['mail_port'];
                $encryption = $credentials['encryption'];
                $mailUsername = $credentials['mail_username'];
                $mailPassword = $credentials['mail_password'];
                $mailFromEmail = $credentials['mail_from'];
                $mailFromName = $credentials['mail_from_name'];
                $mailApiKey = $credentials['mail_api_key'];
            } else {
                $mailType = Setting::getSetting('mail_type') ?: 'smtp';
                $hostAddress = Setting::getSetting('host_address');
                $mailPort = Setting::getSetting('mail_port');
                $encryption = Setting::getSetting('encryption') ?: 'tls';
                $mailUsername = Setting::getSetting('mail_username');
                $mailPassword = Setting::getSetting('mail_password');
                $mailFromEmail = Setting::getSetting('mail_from');
                $mailFromName = Setting::getSetting('mail_from_name');
                $mailApiKey = Setting::getSetting('mail_api_key');
            }
            
            $config = [
                'host' => $hostAddress,
                'port' => $mailPort,
                'encryption' => $encryption,
                'username' => $mailUsername,
                'password' => $mailPassword,
                'fromEmail' => $mailFromEmail,
                'fromName' => $mailFromName,
                'mailApiKey' => $mailApiKey,
                'to' => $to,
                'subject' => $subject,
                'result' => $result,
                'type' => $type,
                'other_arr' => $other_arr,
            ];

            switch ($mailType) {
                case 'Mailgun':
                    return $this->sendViaMailgun($config);
                case 'Sendinblue':
                    return $this->sendViaSendinblue($config);
                case 'SMTP':
                    return $this->sendViaSMTP($config);
                default:
                    return [
                        'status' => 'Error',
                        'message' => 'Invalid mail type: ' . $mailType
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Email sending failed', ['exception' => $e]);
            return [
                'status' => 'Error',
                'message' => 'Email sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send email via SMTP
     *
     * @param array $config
     * @return array
     */
    private function sendViaSMTP($config)
    {
        try {
            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $config['host'],
                'port' => $config['port'],
                'encryption' => $config['encryption'],
                'username' => $config['username'],
                'password' => $config['password'],
            ]);
            Config::set('mail.default', 'smtp');
            Config::set('mail.from.address', $config['fromEmail']);
            Config::set('mail.from.name', $config['fromName']);
            app()->forgetInstance('mailer');

            $template = $this->getEmailTemplate($config['type']);

            if($config['type'] === 'Sale') {
                $data = [
                    'subject' => $config['subject'],
                    'customerName' => $config['result']->customer->name ?? '',
                    'branchName' => $config['result']->branch->branch_name ?? '',
                    'date' => isset($config['result']->order_date) ? \Carbon\Carbon::parse($config['result']->order_date)->format($config['result']->company->date_format) : '',
                    'status' => $config['result']->order_status ?? '',
                    'totalPayable' => $config['result']->company->currency . ' ' . $config['result']->total_payable ?? '',
                    'totalPaid' => $config['result']->company->currency . ' ' . $config['result']->total_paid ?? '',
                    'totalDue' => $config['result']->company->currency . ' ' . $config['result']->total_due ?? '',
                    'referenceNo' => $config['result']->reference_no ?? '',
                    'companyName' => $config['result']->company->name ?? ''
                ];
            } else if($config['type'] === 'Booking') {
                $data = [
                    'subject' => $config['subject'],    
                    'customerName' => $config['result']->customer->name ?? '',
                    'branchName' => $config['result']->branch->branch_name ?? '',
                    'date' => isset($config['result']->date) ? \Carbon\Carbon::parse($config['result']->date)->format($config['result']->company->date_format) : '',
                    'status' => $config['result']->status ?? '',
                    'referenceNo' => $config['result']->reference_no ?? '',
                    'companyName' => $config['result']->company->name ?? '',    
                    'note' => $config['result']->note ?? '',
                ];
            } else if($config['type'] === 'Package Usage') {
                $data = [
                    'subject' => $config['subject'],
                    'branchName' => $config['result']->branch->branch_name ?? '',
                    'companyName' => $config['result']->company->name ?? '',
                    'package_info' => $config['result']->package_info,
                ];
            } else if($config['type'] === 'Forgot Password') {
                $data = [
                    'subject' => $config['subject'],
                    'companyName' => $config['result']->company->name ?? '',
                    'customerName' => $config['result']->name ?? '',
                    'tempPassword' => $config['other_arr']['tempPassword'] ?? '',
                ];
            }
            Mail::send($template, $data, function (Message $message) use ($config) {
                $message->to($config['to'])
                    ->subject($config['subject'])
                    ->from($config['fromEmail'], $config['fromName']);
            });
            return [
                'status' => 'Success',
                'message' => 'Email sent successfully via SMTP'
            ];
        } catch (\Exception $e) {
            Log::error('SMTP email failed', ['exception' => $e]);
            return [
                'status' => 'Error',
                'message' => 'SMTP email sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send email via Mailgun
     *
     * @param array $config
     * @return array
     */
    private function sendViaMailgun($config)
    {
        try {
            // Configure mailgun settings
            Config::set('services.mailgun', [
                'domain' => parse_url($config['fromEmail'], PHP_URL_HOST) ?: 'mg.yourdomain.com',
                'secret' => $config['mailApiKey'],
            ]);

            Config::set('mail.mailers.mailgun', [
                'transport' => 'mailgun',
            ]);

            Config::set('mail.from', [
                'address' => $config['fromEmail'],
                'name' => $config['fromName'],
            ]);

            $template = $this->getEmailTemplate($config['type']);

            if($config['type'] === 'Sale') {
                $data = [
                    'subject' => $config['subject'],
                    'customerName' => $config['result']->customer->name ?? '',
                    'branchName' => $config['result']->branch->branch_name ?? '',
                    'date' => isset($config['result']->order_date) ? \Carbon\Carbon::parse($config['result']->order_date)->format($config['result']->company->date_format) : '',
                    'status' => $config['result']->order_status ?? '',
                    'totalPayable' => $config['result']->company->currency . ' ' . $config['result']->total_payable ?? '',
                    'totalPaid' => $config['result']->company->currency . ' ' . $config['result']->total_paid ?? '',
                    'totalDue' => $config['result']->company->currency . ' ' . $config['result']->total_due ?? '',
                    'referenceNo' => $config['result']->reference_no ?? '',
                    'companyName' => $config['result']->company->name ?? ''
                ];
            } else if($config['type'] === 'Booking') {
                $data = [
                    'subject' => $config['subject'],    
                    'customerName' => $config['result']->customer->name ?? '',
                    'branchName' => $config['result']->branch->branch_name ?? '',
                    'date' => isset($config['result']->date) ? \Carbon\Carbon::parse($config['result']->date)->format($config['result']->company->date_format) : '',
                    'status' => $config['result']->status ?? '',
                    'referenceNo' => $config['result']->reference_no ?? '',
                    'companyName' => $config['result']->company->name ?? '',    
                    'note' => $config['result']->note ?? '',
                ];
            } else if($config['type'] === 'Package Usage') {
                // Get package details
                $data = [
                    'subject' => $config['subject'],
                    'branchName' => $config['result']->branch->branch_name ?? '',
                    'companyName' => $config['result']->company->name ?? '',
                    'package_info' => $config['result']->package_info,
                ];
            } else if($config['type'] === 'Forgot Password') {
                $data = [
                    'subject' => $config['subject'],
                    'companyName' => $config['result']->company->name ?? '',
                    'customerName' => $config['result']->name ?? '',
                    'tempPassword' => $config['other_arr']['tempPassword'] ?? '',
                ];
            }

            // Send email using template
            Mail::send($template, $data, function (Message $message) use ($config) {
                $message->to($config['to'])
                        ->subject($config['subject'])
                        ->from($config['fromEmail'], $config['fromName']);
            });

            return [
                'status' => 'Success',
                'message' => 'Email sent successfully via Mailgun'
            ];
        } catch (\Exception $e) {
            Log::error('Mailgun email failed', ['exception' => $e]);
            return [
                'status' => 'Error',
                'message' => 'Mailgun email sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send email via Sendinblue
     *
     * @param array $config
     * @return array
     */
    private function sendViaSendinblue($config)
    {
        try {
            $template = $this->getEmailTemplate($config['type']);

            if($config['type'] === 'Sale') {
                $data = [
                    'subject' => $config['subject'],
                    'customerName' => $config['result']->customer->name ?? '',
                    'branchName' => $config['result']->branch->branch_name ?? '',
                    'date' => isset($config['result']->order_date) ? \Carbon\Carbon::parse($config['result']->order_date)->format($config['result']->company->date_format) : '',
                    'status' => $config['result']->order_status ?? '',
                    'totalPayable' => $config['result']->company->currency . ' ' . $config['result']->total_payable ?? '',
                    'totalPaid' => $config['result']->company->currency . ' ' . $config['result']->total_paid ?? '',
                    'totalDue' => $config['result']->company->currency . ' ' . $config['result']->total_due ?? '',
                    'referenceNo' => $config['result']->reference_no ?? '',
                    'companyName' => $config['result']->company->name ?? ''
                ];
            } else if($config['type'] === 'Booking') {
                $data = [
                    'subject' => $config['subject'],    
                    'customerName' => $config['result']->customer->name ?? '',
                    'branchName' => $config['result']->branch->branch_name ?? '',
                    'date' => isset($config['result']->date) ? \Carbon\Carbon::parse($config['result']->date)->format($config['result']->company->date_format) : '',
                    'status' => $config['result']->status ?? '',
                    'referenceNo' => $config['result']->reference_no ?? '',
                    'companyName' => $config['result']->company->name ?? '',    
                    'note' => $config['result']->note ?? '',
                ];
            } else if($config['type'] === 'Package Usage') {
                $data = [
                    'subject' => $config['subject'],
                    'branchName' => $config['result']->branch->branch_name ?? '',
                    'companyName' => $config['result']->company->name ?? '',
                    'package_info' => $config['result']->package_info,
                ];
            } else if($config['type'] === 'Forgot Password') {
                $data = [
                    'subject' => $config['subject'],
                    'companyName' => $config['result']->company->name ?? '',
                    'customerName' => $config['result']->name ?? '',
                    'tempPassword' => $config['other_arr']['tempPassword'] ?? '',
                ];
            }
            
            $response = Http::withHeaders([
                'api-key' => $config['mailApiKey'],
                'content-type' => 'application/json'
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => $config['fromName'],
                    'email' => $config['username']
                ],
                'to' => [
                    [
                        'email' => $config['to']
                    ]
                ],
                'subject' => $config['subject'],
                'htmlContent' => view($template, $data)->render(),
            ]);
            
            if ($response->successful()) {
                Log::info('Sendinblue API email sent', ['messageId' => $response->json()['messageId']]);
                return [
                    'status' => 'Success',
                    'message' => 'Email sent successfully via Sendinblue API',
                    'messageId' => $response->json()['messageId']
                ];
            }

            Log::error('Sendinblue API send failed', ['response' => $response->body()]);
            return [
                'status' => 'Error', 
                'message' => 'Failed to send email via Sendinblue API',
                'details' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('Sendinblue API exception', ['exception' => $e]);
            return [
                'status' => 'Error',
                'message' => 'Sendinblue API exception: ' . $e->getMessage()
            ];
        }
        
    }

    /**
     * Get email template based on type
     *
     * @param string $type
     * @return string
     */
    private function getEmailTemplate($type)
    {
        switch ($type) {
            case 'Sale':
                return 'emails.sale';
            case 'Booking':
                return 'emails.booking';
            case 'Package Usage':
                return 'emails.package-usage';
            case 'Forgot Password':
                return 'emails.forgot-password';
            default:
                return 'emails.booking';
        }
    }

    /**
     * Validate email settings
     *
     * @param string $provider
     * @return array
     */
    public function validateSettings($provider)
    {
        $errors = [];

        switch ($provider) {
            case 'SMTP':
                if (!Setting::getSetting('host_address')) {
                    $errors[] = 'SMTP Host Address is required';
                }
                if (!Setting::getSetting('mail_port')) {
                    $errors[] = 'SMTP Port is required';
                }
                if (!Setting::getSetting('mail_username')) {
                    $errors[] = 'SMTP Username is required';
                }
                if (!Setting::getSetting('mail_password')) {
                    $errors[] = 'SMTP Password is required';
                }
                if (!Setting::getSetting('mail_from')) {
                    $errors[] = 'From Email is required';
                }
                if (!Setting::getSetting('mail_from_name')) {
                    $errors[] = 'From Name is required';
                }
                break;
            case 'Mailgun':
                if (!Setting::getSetting('mail_api_key')) {
                    $errors[] = 'Mailgun API Key is required';
                }
                if (!Setting::getSetting('mail_from')) {
                    $errors[] = 'From Email is required';
                }
                if (!Setting::getSetting('mail_from_name')) {
                    $errors[] = 'From Name is required';
                }
                break;
            case 'Sendinblue':
                if (!Setting::getSetting('mail_api_key')) {
                    $errors[] = 'Sendinblue API Key is required';
                }
                if (!Setting::getSetting('mail_from')) {
                    $errors[] = 'From Email is required';
                }
                if (!Setting::getSetting('mail_from_name')) {
                    $errors[] = 'From Name is required';
                }
                break;
            default:
                $errors[] = 'Invalid email provider';
        }

        return $errors;
    }
} 