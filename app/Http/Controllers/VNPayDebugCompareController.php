<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\VNPaySandboxService;
use Illuminate\Support\Facades\Log;

class VNPayDebugCompareController extends Controller
{
    private $vnpTmnCode = "K83BDEI1";
    private $vnpHashSecret = "5LIRBPPT6FA7U16PEHMYGF5YP3XLUWBL";
    private $vnpUrl = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

    public function compareImplementations(Request $request)
    {
        $testOrderId = 123;
        $testAmount = 10000;
        $testOrderInfo = "Test payment";
        
        // Method 1: VNPaySandboxController logic
        $controller_result = $this->createPaymentUrlController($testOrderId, $testAmount, $testOrderInfo);
        
        // Method 2: VNPaySandboxService logic
        $service = new VNPaySandboxService();
        $service_result = $service->createPaymentUrl($testOrderId, $testAmount, $testOrderInfo, 'vn', '', '127.0.0.1');
        
        return view('debug.vnpay-compare', [
            'controller_result' => $controller_result,
            'service_result' => $service_result,
            'test_data' => [
                'order_id' => $testOrderId,
                'amount' => $testAmount,
                'order_info' => $testOrderInfo
            ]
        ]);
    }
    
    private function createPaymentUrlController($orderId, $amount, $orderInfo)
    {
        try {
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            
            $vnp_TxnRef = $orderId; // Sử dụng orderId thay vì random
            $vnp_Amount = $amount;
            $vnp_Locale = 'vn';
            $vnp_BankCode = '';
            $vnp_IpAddr = '127.0.0.1';
            
            $startTime = date("YmdHis");
            $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $this->vnpTmnCode,
                "vnp_Amount" => $vnp_Amount * 100,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => $startTime,
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $orderInfo,
                "vnp_OrderType" => "other",
                "vnp_ReturnUrl" => "http://127.0.0.1:8000/vnpay/return",
                "vnp_TxnRef" => $vnp_TxnRef,
                "vnp_ExpireDate" => $expire
            );

            if (!empty($vnp_BankCode)) {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url_final = $this->vnpUrl . "?" . $query;
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnpHashSecret);
            $vnp_Url_final .= 'vnp_SecureHash=' . $vnpSecureHash;

            return [
                'url' => $vnp_Url_final,
                'input_data' => $inputData,
                'hash_data' => $hashdata,
                'secure_hash' => $vnpSecureHash,
                'method' => 'Controller'
            ];

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'method' => 'Controller'
            ];
        }
    }
}
