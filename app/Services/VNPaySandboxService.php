<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VNPaySandboxService
{
    private $vnpUrl;
    private $vnpReturnUrl;
    private $vnpTmnCode;
    private $vnpHashSecret;
    private $vnpVersion;
    private $vnpApiUrl;

    public function __construct()
    {
        $this->vnpUrl = config('vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $this->vnpReturnUrl = 'http://127.0.0.1:8000/vnpay/return';
        $this->vnpTmnCode = config('vnpay.tmn_code', 'K83BDEI1');
        $this->vnpHashSecret = config('vnpay.hash_secret', '5LIRBPPT6FA7U16PEHMYGF5YP3XLUWBL');
        $this->vnpVersion = config('vnpay.version', '2.1.0');
        $this->vnpApiUrl = config('vnpay.api_url', 'http://sandbox.vnpayment.vn/merchant_webapi/merchant.html');
        
        // Set timezone như trong sandbox
        date_default_timezone_set('Asia/Ho_Chi_Minh');
    }

    /**
     * Tạo URL thanh toán VNPay theo logic sandbox
     */
    public function createPaymentUrl($orderId, $amount, $orderInfo = null, $locale = 'vn', $bankCode = '', $ipAddr = null)
    {
        try {
            // Validate input
            if (empty($this->vnpTmnCode) || empty($this->vnpHashSecret)) {
                throw new \Exception('VNPay configuration is missing');
            }

            if ($amount <= 0) {
                throw new \Exception('Invalid payment amount');
            }

            if (empty($orderId)) {
                throw new \Exception('Order ID is required');
            }

            // Tạo thời gian theo sandbox
            $startTime = date("YmdHis");
            $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));
            
            // Lấy IP
            if (!$ipAddr) {
                $ipAddr = request()->ip() ?: '127.0.0.1';
            }
            
            // Thông tin đơn hàng
            if (!$orderInfo) {
                $orderInfo = "Thanh toan GD:" . $orderId;
            }

            // Tạo input data theo đúng sandbox
            $inputData = array(
                "vnp_Version" => $this->vnpVersion,
                "vnp_TmnCode" => $this->vnpTmnCode,
                "vnp_Amount" => $amount * 100, // VNPay yêu cầu nhân 100
                "vnp_Command" => "pay",
                "vnp_CreateDate" => $startTime,
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $ipAddr,
                "vnp_Locale" => $locale,
                "vnp_OrderInfo" => $orderInfo,
                "vnp_OrderType" => "other",
                "vnp_ReturnUrl" => $this->vnpReturnUrl,
                "vnp_TxnRef" => intval($orderId),
                "vnp_ExpireDate" => $expire
            );

            // Thêm bank code nếu có
            if (isset($bankCode) && $bankCode != "") {
                $inputData['vnp_BankCode'] = $bankCode;
            }

            // Sort theo key - QUAN TRỌNG
            ksort($inputData);
            
            // Tạo query string và hash data theo sandbox
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

            // Tạo URL và hash
            $vnpUrl = $this->vnpUrl . "?" . $query;
            if (isset($this->vnpHashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnpHashSecret);
                $vnpUrl .= 'vnp_SecureHash=' . $vnpSecureHash;
            }
            
            // Log để debug
            Log::info('VNPay Sandbox Payment URL Generated', [
                'order_id' => $orderId,
                'amount' => $amount,
                'hash_data' => $hashdata,
                'secure_hash' => substr($vnpSecureHash, 0, 16) . '...',
                'url_length' => strlen($vnpUrl)
            ]);

            return $vnpUrl;
            
        } catch (\Exception $e) {
            Log::error('VNPay Sandbox createPaymentUrl error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate response từ VNPay theo logic sandbox
     */
    public function validateResponse($inputData)
    {
        try {
            if (empty($inputData) || !isset($inputData['vnp_SecureHash'])) {
                Log::error('VNPay response data invalid', ['data' => $inputData]);
                return false;
            }

            $vnp_SecureHash = $inputData['vnp_SecureHash'];
            unset($inputData['vnp_SecureHash']);
            ksort($inputData);
            
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $this->vnpHashSecret);
            $isValid = ($secureHash == $vnp_SecureHash);
            
            Log::info('VNPay Sandbox Response Validation', [
                'is_valid' => $isValid,
                'response_code' => $inputData['vnp_ResponseCode'] ?? 'N/A',
                'txn_ref' => $inputData['vnp_TxnRef'] ?? 'N/A',
                'hash_data' => $hashData,
                'calculated_hash' => substr($secureHash, 0, 16) . '...',
                'received_hash' => substr($vnp_SecureHash, 0, 16) . '...'
            ]);

            return $isValid;
            
        } catch (\Exception $e) {
            Log::error('VNPay Sandbox validateResponse error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Xử lý IPN từ VNPay
     */
    public function processIPN($inputData)
    {
        $returnData = array();
        
        try {
            $vnp_SecureHash = $inputData['vnp_SecureHash'];
            unset($inputData['vnp_SecureHash']);
            ksort($inputData);
            
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $this->vnpHashSecret);
            $vnpTranId = $inputData['vnp_TransactionNo'] ?? '';
            $vnp_BankCode = $inputData['vnp_BankCode'] ?? '';
            $vnp_Amount = ($inputData['vnp_Amount'] ?? 0) / 100;
            $orderId = $inputData['vnp_TxnRef'] ?? '';

            Log::info('VNPay IPN Processing', [
                'order_id' => $orderId,
                'amount' => $vnp_Amount,
                'transaction_id' => $vnpTranId,
                'bank_code' => $vnp_BankCode,
                'response_code' => $inputData['vnp_ResponseCode'] ?? 'N/A'
            ]);

            // Kiểm tra checksum
            if ($secureHash == $vnp_SecureHash) {
                // Ở đây bạn cần tích hợp với Laravel để:
                // 1. Kiểm tra đơn hàng trong database
                // 2. Cập nhật trạng thái đơn hàng
                // 3. Cập nhật trạng thái thanh toán
                
                if ($inputData['vnp_ResponseCode'] == '00' && 
                    isset($inputData['vnp_TransactionStatus']) && 
                    $inputData['vnp_TransactionStatus'] == '00') {
                    // Thanh toán thành công
                    $returnData['RspCode'] = '00';
                    $returnData['Message'] = 'Confirm Success';
                } else {
                    // Thanh toán thất bại
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Order payment failed';
                }
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Invalid signature';
            }
        } catch (\Exception $e) {
            Log::error('VNPay IPN processing error: ' . $e->getMessage());
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknown error';
        }

        return $returnData;
    }

    /**
     * Lấy thông tin giao dịch (nếu cần)
     */
    public function getTransactionInfo($orderId, $transDate)
    {
        // Implementation cho việc query thông tin giao dịch từ VNPay
        // Sử dụng $this->vnpApiUrl
    }

    /**
     * Lấy trạng thái giao dịch từ response code
     */
    public function getTransactionStatus($responseCode)
    {
        $statusMap = [
            '00' => 'success',
            '07' => 'suspicious',
            '09' => 'not_registered',
            '10' => 'authentication_failed',
            '11' => 'timeout',
            '12' => 'account_locked',
            '13' => 'wrong_otp',
            '24' => 'cancelled',
            '51' => 'insufficient_funds',
            '65' => 'limit_exceeded',
            '75' => 'bank_maintenance',
            '79' => 'password_failed',
            '99' => 'other_error'
        ];

        return $statusMap[$responseCode] ?? 'unknown';
    }
}
