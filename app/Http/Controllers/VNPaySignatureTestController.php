<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VNPaySignatureTestController extends Controller
{
    private $vnpHashSecret = '5LIRBBPT6FA7U16PEHMYGF5YP3XLUWBL';
    
    public function testSignature()
    {
        $testParams = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => 'K83BDEI1',
            'vnp_Amount' => '80000000',
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => '32',
            'vnp_OrderInfo' => 'Thanh toan don hang #32',
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => 'http://127.0.0.1:8000/vnpay/return',
            'vnp_IpAddr' => '127.0.0.1',
            'vnp_CreateDate' => '20250115101500',
            'vnp_ExpireDate' => '20250115104500'
        ];
        
        // Sort parameters
        ksort($testParams);
        
        // Test different encoding methods
        $results = [];
        
        // Method 1: No encoding for hash data (VNPay standard)
        $hashdata1 = "";
        foreach ($testParams as $key => $value) {
            if (!empty($value)) {
                $hashdata1 .= $key . "=" . $value . "&";
            }
        }
        $hashdata1 = rtrim($hashdata1, '&');
        $hash1 = hash_hmac('sha512', $hashdata1, $this->vnpHashSecret);
        $results['method_1'] = [
            'description' => 'Raw values (VNPay standard)',
            'hash_data' => $hashdata1,
            'signature' => $hash1
        ];
        
        // Method 2: URL encoded values
        $hashdata2 = "";
        foreach ($testParams as $key => $value) {
            if (!empty($value)) {
                $hashdata2 .= $key . "=" . urlencode($value) . "&";
            }
        }
        $hashdata2 = rtrim($hashdata2, '&');
        $hash2 = hash_hmac('sha512', $hashdata2, $this->vnpHashSecret);
        $results['method_2'] = [
            'description' => 'URL encoded values',
            'hash_data' => $hashdata2,
            'signature' => $hash2
        ];
        
        // Method 3: Double encoding
        $hashdata3 = "";
        foreach ($testParams as $key => $value) {
            if (!empty($value)) {
                $hashdata3 .= urlencode($key) . "=" . urlencode($value) . "&";
            }
        }
        $hashdata3 = rtrim($hashdata3, '&');
        $hash3 = hash_hmac('sha512', $hashdata3, $this->vnpHashSecret);
        $results['method_3'] = [
            'description' => 'Double URL encoding',
            'hash_data' => $hashdata3,
            'signature' => $hash3
        ];
        
        return view('vnpay-signature-test', [
            'results' => $results,
            'secret_preview' => substr($this->vnpHashSecret, 0, 10) . '...',
            'test_params' => $testParams
        ]);
    }
    
    public function validateFromUrl(Request $request)
    {
        $url = $request->input('test_url');
        
        if (!$url) {
            return response()->json(['error' => 'URL is required']);
        }
        
        // Parse URL
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $params);
        
        $receivedHash = $params['vnp_SecureHash'] ?? '';
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
        
        // Sort and build hash
        ksort($params);
        $hashdata = "";
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $hashdata .= $key . "=" . $value . "&";
            }
        }
        $hashdata = rtrim($hashdata, '&');
        
        $calculatedHash = hash_hmac('sha512', $hashdata, $this->vnpHashSecret);
        
        return response()->json([
            'url' => $url,
            'received_hash' => $receivedHash,
            'calculated_hash' => $calculatedHash,
            'is_valid' => $receivedHash === $calculatedHash,
            'hash_data' => $hashdata,
            'parameters' => $params
        ]);
    }
}
