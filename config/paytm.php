<?php

return [
    'mid' => env('PAYTM_MERCHANT_ID'),
    'key' => env('PAYTM_MERCHANT_KEY'),
    'website' => env('PAYTM_MERCHANT_WEBSITE'),
    'industry_type' => env('PAYTM_INDUSTRY_TYPE_ID'),
    'channel' => env('PAYTM_CHANNEL_ID'),
    'callback_url' => env('PAYTM_CALLBACK_URL'),
    'env' => env('PAYTM_ENVIRONMENT', 'staging'),
];

?>