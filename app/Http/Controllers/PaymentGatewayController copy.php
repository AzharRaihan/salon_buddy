<?php

namespace App\Http\Controllers;

use PayPal\Api\Payer;
use Razorpay\Api\Api;
use PayPal\Api\Amount;
use App\Models\Setting;
use PayPal\Api\Payment;
use App\Traits\ApiResponse;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use Illuminate\Http\Request;
use PayPal\Api\RedirectUrls;
use Illuminate\Support\Facades\Auth;
use PayPal\Auth\OAuthTokenCredential;

class PaymentGatewayController extends Controller
{
    use ApiResponse;

    public $companyPaymentConfig;

    public function __construct()
    {
        $this->companyPaymentConfig = $this->getCompanyPaymentConfig();
    }


    public function getCompanyPaymentConfig()
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
        return $settings;
    }


    public function createRazorpayOrder(Request $request)
    {
        $api = new Api($this->companyPaymentConfig['razorpay_key'], $this->companyPaymentConfig['razorpay_secret']);
        $orderData = [
            'receipt'         => 'order_rcptid_' . uniqid(),
            'amount'          => (int)$request->amount, // amount in paise (e.g., 100 INR = 10000)
            'currency'        => $request->currency ?? 'INR',
            'payment_capture' => 1 // auto capture
        ];
        $razorpayOrder = $api->order->create($orderData);
        return $this->successResponse([
            'order_id' => $razorpayOrder['id'],
            'amount'   => $razorpayOrder['amount'],
            'currency' => $razorpayOrder['currency'],
            'rk' => $this->companyPaymentConfig['razorpay_key'],
            
        ], 'Razorpay order created');
    }

    public function verifyRazorpayPayment(Request $request)
    {
        $signature = $request->razorpay_signature;
        $orderId   = $request->razorpay_order_id;
        $paymentId = $request->razorpay_payment_id;

        $api = new Api($this->companyPaymentConfig['razorpay_key'], $this->companyPaymentConfig['razorpay_secret']);

        try {
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature
            ];
            $api->utility->verifyPaymentSignature($attributes);

            // Mark order as paid in your DB here

            return $this->successResponse(['success' => true], 'Payment verified successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Payment verification failed: ' . $e->getMessage());
        }
    }

    // Stripe - Create Payment Intent (for embedded form)
    public function createStripePaymentIntent(Request $request)
    {
        \Stripe\Stripe::setApiKey($this->companyPaymentConfig['stripe_secret']);

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int)$request->payment_amount, // in cents
                'currency' => $request->currency ?? 'usd',
                'metadata' => [
                    'order_id' => $request->order_id ?? 'pos_order_' . uniqid(),
                    'customer_name' => $request->customer_name ?? 'POS Customer'
                ]
            ]);
            return $this->successResponse([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'window_open_id' => $this->companyPaymentConfig['stripe_key']
            ], 'Stripe payment intent created');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create payment intent: ' . $e->getMessage());
        }
    }

    // Stripe - Create Session (for redirect - keeping for compatibility)
    public function createStripeSession(Request $request)
    {
        \Stripe\Stripe::setApiKey($this->companyPaymentConfig['stripe_secret']);

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $request->currency ?? 'usd',
                    'product_data' => [
                        'name' => 'POS Payment',
                    ],
                    'unit_amount' => (int)$request->payment_amount, // in cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/payment-success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('/payment-cancel'),
        ]);

        return $this->successResponse(['session_id' => $session->id], 'Stripe session created');
    }

    public function verifyStripePayment(Request $request)
    {
        \Stripe\Stripe::setApiKey($this->companyPaymentConfig['stripe_secret']);
        
        try {
            if ($request->payment_intent_id) {
                // Verify payment intent
                $paymentIntent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);
                if ($paymentIntent->status === 'succeeded') {
                    return $this->successResponse(['success' => true], 'Payment verified successfully');
                } else {
                    return $this->errorResponse('Payment not completed');
                }
            } else if ($request->session_id) {
                // Verify session (for redirect method)
                $session = \Stripe\Checkout\Session::retrieve($request->session_id);
                if ($session->payment_status === 'paid') {
                    return $this->successResponse(['success' => true], 'Payment verified successfully');
                } else {
                    return $this->errorResponse('Payment not completed');
                }
            } else {
                return $this->errorResponse('Missing payment intent ID or session ID');
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Payment verification failed: ' . $e->getMessage());
        }
    }

    // PayPal
    public function createPaypalOrder(Request $request)
    {
        // dd($request->all());
        
        try {
            $apiContext = new ApiContext(
                new OAuthTokenCredential(
                    $this->companyPaymentConfig['paypal_client_id'],
                    $this->companyPaymentConfig['paypal_client_secret']
                )
            );
            $apiContext->setConfig(['mode' => $this->companyPaymentConfig['paypal_mode']]);

            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $amount = new Amount();
            $amount->setTotal(number_format((float)$request->payment_amount, 2, '.', ''));
            
            $amount->setCurrency($request->currency ?? 'USD');

            $transaction = new Transaction();
            $transaction->setAmount($amount);
            $transaction->setDescription('POS Payment - Order: ' . ($request->order_id ?? 'Unknown'));

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl(url('/payment-success'))
                        ->setCancelUrl(url('/payment-cancel'));

            $payment = new Payment();
            $payment->setIntent('sale')
                    ->setPayer($payer)
                    ->setTransactions([$transaction])
                    ->setRedirectUrls($redirectUrls);

            $payment->create($apiContext);

            return $this->successResponse([
                'payment_url' => $payment->getApprovalLink(),
                'payment_id' => $payment->getId()
            ], 'PayPal order created');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create PayPal order: ' . $e->getMessage());
        }
    }

    public function verifyPaypalPayment(Request $request)
    {
        try {
            $apiContext = new ApiContext(
                new OAuthTokenCredential(
                    $this->companyPaymentConfig['paypal_client_id'],
                    $this->companyPaymentConfig['paypal_client_secret']
                )
            );
            $apiContext->setConfig(['mode' => $this->companyPaymentConfig['paypal_mode']]);

            $paymentId = $request->paymentId;
            $payerId = $request->PayerID;

            if (!$paymentId || !$payerId) {
                return $this->errorResponse('Missing payment ID or payer ID');
            }

            $payment = Payment::get($paymentId, $apiContext);
            $execution = new \PayPal\Api\PaymentExecution();
            $execution->setPayerId($payerId);

            $result = $payment->execute($execution, $apiContext);
            // Mark order as paid in your DB here
            return $this->successResponse(['success' => true], 'Payment verified successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Payment verification failed: ' . $e->getMessage());
        }
    }

    // Check PayPal payment status
    public function checkPaypalPaymentStatus(Request $request)
    {
        try {
            $orderId = $request->order_id;
            
            // You should implement your own logic to check payment status
            // This is a placeholder - you need to store and check payment status in your database
            // For now, we'll return a mock response
            
            // In a real implementation, you would:
            // 1. Check your database for the order status
            // 2. Optionally call PayPal API to verify payment
            // 3. Return the actual status
            
            return $this->successResponse([
                'success' => true,
                'status' => 'completed',
                'order_id' => $orderId
            ], 'Payment status checked');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to check payment status: ' . $e->getMessage());
        }
    }

    public function paymentSuccess(Request $request)
    {
        return $this->successResponse(['success' => true], 'Payment successful');
    }

    // Debug method to check PayPal configuration
    public function debugPaypalConfig()
    {
        try {
            $config = [
                'client_id' => $this->companyPaymentConfig['paypal_client_id'],
                'secret' => $this->companyPaymentConfig['paypal_client_secret'] ? 'SET' : 'NOT SET',
                'mode' => $this->companyPaymentConfig['paypal_mode'],
            ];

            return $this->successResponse($config, 'PayPal configuration debug info');
        } catch (\Exception $e) {
            return $this->errorResponse('Debug error: ' . $e->getMessage());
        }
    }

    // Paytm
    public function createPaytmOrder(Request $request)
    {
        try {
            $order_id = 'ORD_' . time();
            $amount = $request->amount;

            $data = [
                'MID' => config('services.paytm.mid'),
                'ORDER_ID' => $order_id,
                'CUST_ID' => 'CUST_' . time(),
                'INDUSTRY_TYPE_ID' => config('services.paytm.industry_type'),
                'CHANNEL_ID' => config('services.paytm.channel'),
                'TXN_AMOUNT' => $amount,
                'WEBSITE' => config('services.paytm.website'),
                'CALLBACK_URL' => url(config('services.paytm.callback_url')),
            ];

            // Check if Paytm SDK is available
            if (!class_exists('\Paytm\Checksum\Checksum')) {
                return $this->errorResponse('Paytm SDK not installed. Please install: composer require paytm/paytmchecksum');
            }

            $checksum = \Paytm\Checksum\Checksum::generateSignature($data, config('services.paytm.key'));
            $data['CHECKSUMHASH'] = $checksum;

            // Return JSON response instead of view
            return $this->successResponse([
                'order_id' => $order_id,
                'amount' => $amount,
                'payment_data' => $data
            ], 'Paytm order created');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create Paytm order: ' . $e->getMessage());
        }
    }

    public function verifyPaytmPayment(Request $request)
    {
        try {
            $data = $request->all();

            // Check if Paytm SDK is available
            if (!class_exists('\Paytm\Checksum\Checksum')) {
                return $this->errorResponse('Paytm SDK not installed');
            }

            $isValidChecksum = \Paytm\Checksum\Checksum::verifySignature(
                $data,
                config('services.paytm.key'),
                $data['CHECKSUMHASH'] ?? ''
            );

            if ($isValidChecksum && ($data['RESPCODE'] ?? '') == '01') {
                return $this->successResponse(['success' => true], 'Payment verified successfully');
            } else {
                return $this->errorResponse('Payment verification failed: ' . ($data['RESPMSG'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Paytm verification error: ' . $e->getMessage());
        }
    }

    // Check Paytm payment status
    public function checkPaytmPaymentStatus(Request $request)
    {
        try {
            $orderId = $request->order_id;
            
            // You should implement your own logic to check payment status
            // This is a placeholder - you need to store and check payment status in your database
            
            return $this->successResponse([
                'success' => true,
                'status' => 'completed',
                'order_id' => $orderId
            ], 'Payment status checked');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to check payment status: ' . $e->getMessage());
        }
    }

    // Paystack Integration
    public function createPaystackOrder(Request $request)
    {
        $amount = $request->amount * 100; // Convert to kobo
        $email = $request->email ?? 'customer@example.com';
        $reference = 'PAYSTACK_' . uniqid();

        $url = 'https://api.paystack.co/transaction/initialize';
        $headers = [
            'Authorization: Bearer ' . config('services.paystack.secret'),
            'Cache-Control: no-cache',
        ];

        $fields = [
            'email' => $email,
            'amount' => $amount,
            'reference' => $reference,
            'callback_url' => url('/payment-success'),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($result['status']) {
            return $this->successResponse([
                'authorization_url' => $result['data']['authorization_url'],
                'reference' => $reference
            ], 'Paystack order created');
        } else {
            return $this->errorResponse('Failed to create Paystack order');
        }
    }

    public function verifyPaystackPayment(Request $request)
    {
        $reference = $request->reference;

        $url = 'https://api.paystack.co/transaction/verify/' . $reference;
        $headers = [
            'Authorization: Bearer ' . config('services.paystack.secret'),
            'Cache-Control: no-cache',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($result['status'] && $result['data']['status'] === 'success') {
            return $this->successResponse(['success' => true], 'Payment verified successfully');
        } else {
            return $this->errorResponse('Payment verification failed');
        }
    }
}
