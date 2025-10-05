<?php

namespace App\Http\Controllers;

use Razorpay\Api\Api;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Controllers\OrdersController;
use PaypalServerSdkLib\Models\OrderRequest;
use PaypalServerSdkLib\Models\PurchaseUnitRequest;
use PaypalServerSdkLib\Models\AmountWithBreakdown;
use PaypalServerSdkLib\Models\Money;
use PaypalServerSdkLib\Models\OrderApplicationContext;

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

    // PayPal - Using paypal-server-sdk
    public function createPaypalOrder(Request $request)
    {
        // dd($this->companyPaymentConfig['paypal_client_id']);
        try {
            // Initialize PayPal client using the new SDK
            $client = PaypalServerSdkClientBuilder::init()
                ->clientCredentialsAuthCredentials(
                    ClientCredentialsAuthCredentialsBuilder::init(
                        $this->companyPaymentConfig['paypal_client_id'],
                        $this->companyPaymentConfig['paypal_client_secret']
                    )
                )
                ->environment($this->companyPaymentConfig['paypal_mode'] === 'sandbox' ? Environment::SANDBOX : Environment::PRODUCTION)
                ->build();

            // Get orders controller from the client
            $ordersController = $client->getOrdersController();

            // Create amount with required parameters
            $amount = new AmountWithBreakdown(
                $request->currency ?? 'USD',
                number_format((float)$request->amount, 2, '.', '')
            );
            
            // Create purchase unit with amount
            $purchaseUnit = new PurchaseUnitRequest($amount);
            $purchaseUnit->setDescription('POS Payment - Order: ' . ($request->order_id ?? 'Unknown'));

            // Create order request with required parameters
            $orderRequest = new OrderRequest('CAPTURE', [$purchaseUnit]);

            // Set application context
            $applicationContext = new OrderApplicationContext();
            $applicationContext->setReturnUrl(url('/payment-success'));
            $applicationContext->setCancelUrl(url('/payment-cancel'));
            $applicationContext->setBrandName('Salon Buddy');
            $applicationContext->setLandingPage('NO_PREFERENCE');
            $applicationContext->setUserAction('PAY_NOW');

            $orderRequest->setApplicationContext($applicationContext);

            // Create the order
            $response = $ordersController->createOrder(['body' => $orderRequest]);
            $order = $response->getResult();

            // Get approval URL
            $approvalUrl = '';
            foreach ($order->getLinks() as $link) {
                if ($link->getRel() === 'approve') {
                    $approvalUrl = $link->getHref();
                    break;
                }
            }

            return $this->successResponse([
                'payment_url' => $approvalUrl,
                'order_id' => $order->getId()
            ], 'PayPal order created');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create PayPal order: ' . $e->getMessage());
        }
    }

    // Verify  Paypal
    public function verifyPaypalPayment(Request $request)
    {
        try {
            // Initialize PayPal client using the new SDK
            $client = PaypalServerSdkClientBuilder::init()
                ->clientCredentialsAuthCredentials(
                    ClientCredentialsAuthCredentialsBuilder::init(
                        $this->companyPaymentConfig['paypal_client_id'],
                        $this->companyPaymentConfig['paypal_client_secret']
                    )
                )
                ->environment($this->companyPaymentConfig['paypal_mode'] === 'sandbox' ? Environment::SANDBOX : Environment::PRODUCTION)
                ->build();

            // Get orders controller from the client
            $ordersController = $client->getOrdersController();

            $orderId = $request->order_id;
            $token = $request->token;

            if (!$orderId) {
                return $this->errorResponse('Missing order ID');
            }

            // Capture the order
            $response = $ordersController->captureOrder(['id' => $orderId]);
            $capturedOrder = $response->getResult();

            if ($capturedOrder->getStatus() === 'COMPLETED') {
                return $this->successResponse(['success' => true], 'Payment verified successfully');
            } else {
                return $this->errorResponse('Payment not completed');
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Payment verification failed: ' . $e->getMessage());
        }
    }

    // Check PayPal payment status
    public function checkPaypalPaymentStatus(Request $request)
    {
        try {
            // Initialize PayPal client using the new SDK
            $client = PaypalServerSdkClientBuilder::init()
                ->clientCredentialsAuthCredentials(
                    ClientCredentialsAuthCredentialsBuilder::init(
                        $this->companyPaymentConfig['paypal_client_id'],
                        $this->companyPaymentConfig['paypal_client_secret']
                    )
                )
                ->environment($this->companyPaymentConfig['paypal_mode'] === 'sandbox' ? Environment::SANDBOX : Environment::PRODUCTION)
                ->build();

            // Get orders controller from the client
            $ordersController = $client->getOrdersController();

            $orderId = $request->order_id;
            
            if (!$orderId) {
                return $this->errorResponse('Missing order ID');
            }

            // Get order details from PayPal
            $response = $ordersController->getOrder(['id' => $orderId]);
            $order = $response->getResult();

            $status = $order->getStatus();
            $isCompleted = $status === 'COMPLETED';

            return $this->successResponse([
                'success' => $isCompleted,
                'status' => strtolower($status),
                'order_id' => $orderId,
                'paypal_order_id' => $order->getId()
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
                'environment' => $this->companyPaymentConfig['paypal_mode'] === 'sandbox' ? 'SANDBOX' : 'PRODUCTION',
                'sdk_version' => 'PayPal Server SDK v1.1',
                'package' => 'paypal/paypal-server-sdk'
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
        try {
            // Check if Paystack is enabled and configured
            if (!$this->companyPaymentConfig['paystack_enabled']) {
                return $this->errorResponse('Paystack payment is not enabled');
            }

            $paystackSecret = $this->companyPaymentConfig['paystack_key'];
            if (empty($paystackSecret)) {
                return $this->errorResponse('Paystack secret key is not configured');
            }

            $rawAmount = $request->amount;

            // Remove commas, cast to float, then convert to kobo
            $amount = (int) round(((float) str_replace(',', '', $rawAmount)) * 100);
            $email = $request->email ?? 'customer@example.com';

            $reference = 'PAYSTACK_' . uniqid();

            $url = 'https://api.paystack.co/transaction/initialize';

            $headers = [
                'Authorization: Bearer ' . $paystackSecret,
                'Content-Type: application/json',
                'Cache-Control: no-cache',
            ];
            
            $fields = [
                'email' => $email,
                'amount' => $amount,
                'reference' => $reference,
                'callback_url' => url('/pos'),
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                return $this->errorResponse('Failed to connect to Paystack API');
            }

            $result = json_decode($response, true);

            if ($result && $result['status'] && isset($result['data']['authorization_url'])) {
                return $this->successResponse([
                    'authorization_url' => $result['data']['authorization_url'],
                    'reference' => $reference,
                ], 'Paystack order created');
            } else {
                $errorMessage = $result['message'] ?? 'Failed to create Paystack order';
                return $this->errorResponse($errorMessage);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Paystack order creation failed: ' . $e->getMessage());
        }
    }

    public function verifyPaystackPayment(Request $request)
    {
        try {
            // Check if Paystack is enabled and configured
            if (!$this->companyPaymentConfig['paystack_enabled']) {
                return $this->errorResponse('Paystack payment is not enabled');
            }

            $paystackSecret = $this->companyPaymentConfig['paystack_key'];
            if (empty($paystackSecret)) {
                return $this->errorResponse('Paystack secret key is not configured');
            }

            $reference = $request->reference;
            if (empty($reference)) {
                return $this->errorResponse('Payment reference is required');
            }

            $url = 'https://api.paystack.co/transaction/verify/' . $reference;
            $headers = [
                'Authorization: Bearer ' . $paystackSecret,
                'Content-Type: application/json',
                'Cache-Control: no-cache',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                return $this->errorResponse('Failed to connect to Paystack API');
            }

            $result = json_decode($response, true);

            if ($result && $result['status'] && isset($result['data']['status']) && $result['data']['status'] === 'success') {
                return $this->successResponse(['success' => true], 'Payment verified successfully');
            } else {
                $errorMessage = $result['message'] ?? 'Payment verification failed';
                return $this->errorResponse($errorMessage);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Paystack verification failed: ' . $e->getMessage());
        }
    }
}
