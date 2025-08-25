<?php

return [
    'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'return_url' => env('VNPAY_RETURN_URL', 'http://127.0.0.1:8000/vnpay/return'),
    'tmn_code' => env('VNPAY_TMN_CODE'),
    'hash_secret' => env('VNPAY_HASH_SECRET'),
    'version' => env('VNPAY_VERSION', '2.1.0'),
    'command' => env('VNPAY_COMMAND', 'pay'),
    'currency' => env('VNPAY_CURRENCY', 'VND'),
    'locale' => env('VNPAY_LOCALE', 'vn'),

    // Thêm timezone để dùng trong Service
    'timezone' => env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'),
];