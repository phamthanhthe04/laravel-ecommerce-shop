<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VNPayServiceFixed
{
    private $vnpUrl;
    private $vnpReturnUrl;
    private $vnpTmnCode;
    private $vnpHashSecret;
    private $vnpVersion;

    public function __construct()
    {
        $this->vnpUrl = config('vnpay.vnp_url');
        $this->vnpReturnUrl = config('vnpay.vnp_returnurl');
        $this->vnpTmnCode = config('vnpay.vnp_tmncode');
        $this->vnpHashSecret = config('vnpay.vnp_hashsecret');
        $this->vnpVersion = config('vnpay.vnp_version');
    }

    public function createPaymentUrl($orderId, $amount, $orderInfo, $ipAddress = null)
    {
        try {
            $startTime = date("YmdHis");
            $expire = date('YmdHis', strtotime('+1 hour', strtotime($startTime)));
            
            // Ensure IP address is valid
            $clientIp = $ipAddress ?: request()->ip() ?: '127.0.0.1';
            if (!filter_var($clientIp, FILTER_VALIDATE_IP)) {
                $clientIp = '127.0.0.1';
            }

            // VNPay parameters - EXACTLY as specified by VNPay docs
            $vnpParams = [
                'vnp_Version' => $this->vnpVersion,
                'vnp_Command' => 'pay',
                'vnp_TmnCode' => $this->vnpTmnCode,
                'vnp_Amount' => intval($amount * 100), // Convert to VND cents
                'vnp_CurrCode' => 'VND',
                'vnp_TxnRef' => strval($orderId),
                'vnp_OrderInfo' => $orderInfo,
                'vnp_OrderType' => 'other',
                'vnp_Locale' => 'vn',
                'vnp_ReturnUrl' => $this->vnpReturnUrl,
                'vnp_IpAddr' => $clientIp,
                'vnp_CreateDate' => $startTime,
                'vnp_ExpireDate' => $expire
            ];

            // CRITICAL: Remove any empty/null values
            $cleanParams = [];
            foreach ($vnpParams as $key => $value) {
                if ($value !== null && $value !== '' && $value !== false) {
                    $cleanParams[$key] = strval($value); // Ensure all values are strings
                }
            }

            // Sort parameters alphabetically by key
            ksort($cleanParams);
            
            // Create hash data string - NO URL encoding for hash calculation
            $hashDataParts = [];
            foreach ($cleanParams as $key => $value) {
                $hashDataParts[] = $key . '=' . $value;
            }
            $hashData = implode('&', $hashDataParts);
            
            // Create URL query string - WITH URL encoding for transmission
            $queryParts = [];
            foreach ($cleanParams as $key => $value) {
                $queryParts[] = urlencode($key) . '=' . urlencode($value);
            }
            $queryString = implode('&', $queryParts);
            
            // Generate secure hash using raw hash data
            $secureHash = hash_hmac('sha512', $hashData, $this->vnpHashSecret);
            
            // Final URL with secure hash
            $paymentUrl = $this->vnpUrl . '?' . $queryString . '&vnp_SecureHash=' . $secureHash;
            
            Log::info('VNPay Payment URL Generated (FIXED)', [
                'order_id' => $orderId,
                'amount' => $amount,
                'hash_data' => $hashData,
                'hash_data_length' => strlen($hashData),
                'secure_hash' => substr($secureHash, 0, 16) . '...',
                'parameters_count' => count($cleanParams),
                'url_length' => strlen($paymentUrl)
            ]);

            return $paymentUrl;
            
        } catch (\Exception $e) {
            Log::error('VNPay createPaymentUrl error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function validateResponse($inputData)
    {
        try {
            if (empty($inputData) || !isset($inputData['vnp_SecureHash'])) {
                Log::error('VNPay validation failed: Missing required data');
                return false;
            }

            $receivedHash = $inputData['vnp_SecureHash'];
            
            // Remove hash fields from validation data
            $dataToValidate = $inputData;
            unset($dataToValidate['vnp_SecureHash']);
            unset($dataToValidate['vnp_SecureHashType']);
            
            // Remove empty values
            $cleanData = [];
            foreach ($dataToValidate as $key => $value) {
                if ($value !== null && $value !== '' && $value !== false) {
                    $cleanData[$key] = strval($value);
                }
            }
            
            // Sort parameters
            ksort($cleanData);
            
            // Create hash data string - same method as URL creation
            $hashDataParts = [];
            foreach ($cleanData as $key => $value) {
                $hashDataParts[] = $key . '=' . $value;
            }
            $hashData = implode('&', $hashDataParts);
            
            // Calculate hash
            $calculatedHash = hash_hmac('sha512', $hashData, $this->vnpHashSecret);
            
            $isValid = hash_equals($calculatedHash, $receivedHash);
            
            Log::info('VNPay Response Validation (FIXED)', [
                'is_valid' => $isValid,
                'received_hash' => substr($receivedHash, 0, 16) . '...',
                'calculated_hash' => substr($calculatedHash, 0, 16) . '...',
                'hash_data' => substr($hashData, 0, 200) . '...',
                'response_code' => $inputData['vnp_ResponseCode'] ?? 'unknown'
            ]);
            
            return $isValid;
            
        } catch (\Exception $e) {
            Log::error('VNPay validateResponse error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getTransactionStatus($responseCode)
    {
        $statusMap = [
            '00' => 'success',
            '07' => 'pending',
            '09' => 'failed',
            '10' => 'failed',
            '11' => 'failed',
            '12' => 'failed',
            '13' => 'failed',
            '24' => 'cancelled',
            '51' => 'failed',
            '65' => 'failed',
            '75' => 'failed',
            '79' => 'failed',
            '99' => 'failed'
        ];
        
        return $statusMap[$responseCode] ?? 'failed';
    }
}
