<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VNPayService
{
    private $vnpUrl;
    private $vnpReturnUrl;
    private $vnpTmnCode;
    private $vnpHashSecret;
    private $vnpVersion;

    public function __construct()
{
    $this->vnpUrl = config('vnpay.url');
    $this->vnpReturnUrl = config('vnpay.return_url');
    $this->vnpTmnCode = config('vnpay.tmn_code');
    $this->vnpHashSecret = config('vnpay.hash_secret');
    $this->vnpVersion = config('vnpay.version');

    // Đảm bảo set timezone cho đúng giờ Hà Nội
    date_default_timezone_set(config('vnpay.timezone', 'Asia/Ho_Chi_Minh'));
}

    public function createPaymentUrl($orderId, $amount, $orderInfo, $ipAddress = null)
    {
        try {
            // Validate input
            if (empty($this->vnpTmnCode) || empty($this->vnpHashSecret)) {
                throw new \Exception('VNPay configuration is missing. Please check your environment settings.');
            }

            if ($amount <= 0) {
                throw new \Exception('Invalid payment amount');
            }

            if (empty($orderId)) {
                throw new \Exception('Order ID is required');
            }

            // Log để debug
            Log::info('Creating VNPay payment URL', [
                'order_id' => $orderId,
                'amount' => $amount,
                'return_url' => $this->vnpReturnUrl,
                'ip' => $ipAddress
            ]);

            // Tạo timestamp
            $startTime = date("YmdHis");
            $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

            // Đảm bảo IP address hợp lệ
            $clientIp = $ipAddress ?: request()->ip() ?: '127.0.0.1';
            
            // Validate IP format
            if (!filter_var($clientIp, FILTER_VALIDATE_IP)) {
                $clientIp = '127.0.0.1';
            }

            $vnpParams = [
                'vnp_Version' => $this->vnpVersion,
                'vnp_Command' => 'pay',
                'vnp_TmnCode' => $this->vnpTmnCode,
                'vnp_Amount' => round($amount * 100), // VNPay yêu cầu tính bằng đồng, không có số thập phân
                'vnp_CurrCode' => 'VND',
                'vnp_TxnRef' => $orderId,
                'vnp_OrderInfo' => $orderInfo,
                'vnp_OrderType' => 'other',
                'vnp_Locale' => 'vn',
                'vnp_ReturnUrl' => $this->vnpReturnUrl,
                'vnp_IpAddr' => $clientIp,
                'vnp_CreateDate' => $startTime,
                'vnp_ExpireDate' => $expire
            ];

            // Sắp xếp params theo alphabet
            ksort($vnpParams);
            
            // Tạo hash data và query string - FIX encoding issue
            $hashdata = "";
            $query = "";
            
            foreach ($vnpParams as $key => $value) {
                if (!empty($value) && $value !== '' && $value !== null) {
                    // Để tính hash, sử dụng giá trị raw (không encode)
                    $hashdata .= $key . "=" . $value . "&";
                    // Để tạo URL, encode properly
                    $query .= urlencode($key) . "=" . urlencode($value) . "&";
                }
            }
            
            // Bỏ ký tự & cuối cùng
            $hashdata = rtrim($hashdata, '&');
            $query = rtrim($query, '&');
            
            // Log hash data để debug
            Log::info('VNPay hash calculation', [
                'hash_data' => $hashdata,
                'hash_data_length' => strlen($hashdata),
                'secret_length' => strlen($this->vnpHashSecret)
            ]);
            
            // Tạo secure hash
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnpHashSecret);
            $paymentUrl = $this->vnpUrl . "?" . $query . "&vnp_SecureHash=" . $vnpSecureHash;
            
            // Validate URL length (URLs should not be too long)
            if (strlen($paymentUrl) > 2000) {
                Log::warning('VNPay URL is very long', ['length' => strlen($paymentUrl)]);
            }
            
            // Log URL để debug
            Log::info('VNPay payment URL created successfully', [
                'order_id' => $orderId,
                'amount' => $amount,
                'url_length' => strlen($paymentUrl),
                'secure_hash' => substr($vnpSecureHash, 0, 10) . '...',
                'params_count' => count($vnpParams),
                'expire_time' => $expire,
                'full_url' => $paymentUrl, // Log full URL cho debug
                'parameters' => $vnpParams // Log tất cả parameters
            ]);

            return $paymentUrl;
            
        } catch (\Exception $e) {
            Log::error('VNPay createPaymentUrl error: ' . $e->getMessage(), [
                'order_id' => $orderId ?? null,
                'amount' => $amount ?? null,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function validateResponse($inputData)
    {
        try {
            if (empty($inputData)) {
                Log::error('VNPay response data is empty');
                return false;
            }

            if (empty($inputData['vnp_SecureHash'])) {
                Log::error('VNPay SecureHash missing in response', ['data' => $inputData]);
                return false;
            }

            $vnpSecureHash = $inputData['vnp_SecureHash'];
            
            // Tạo bản sao để không thay đổi dữ liệu gốc
            $dataToValidate = $inputData;
            
            // Loại bỏ secure hash khỏi data để tính toán
            unset($dataToValidate['vnp_SecureHash']);
            unset($dataToValidate['vnp_SecureHashType']);

            // Sắp xếp params
            ksort($dataToValidate);
            
            // Tạo hash data
            $hashdata = "";
            foreach ($dataToValidate as $key => $value) {
                if (!empty($value) && $key !== 'vnp_SecureHash' && $key !== 'vnp_SecureHashType') {
                    $hashdata .= $key . "=" . $value . "&";
                }
            }
            
            $hashdata = rtrim($hashdata, '&');
            
            if (empty($hashdata)) {
                Log::error('VNPay hash data is empty after processing');
                return false;
            }
            
            $secureHash = hash_hmac('sha512', $hashdata, $this->vnpHashSecret);
            
            $isValid = hash_equals($secureHash, $vnpSecureHash);
            
            Log::info('VNPay validation result', [
                'is_valid' => $isValid,
                'calculated_hash' => substr($secureHash, 0, 10) . '...',
                'received_hash' => substr($vnpSecureHash, 0, 10) . '...',
                'hash_data_length' => strlen($hashdata),
                'response_code' => $inputData['vnp_ResponseCode'] ?? 'unknown'
            ]);
            
            if (!$isValid) {
                Log::error('VNPay hash validation failed', [
                    'expected' => substr($secureHash, 0, 20) . '...',
                    'received' => substr($vnpSecureHash, 0, 20) . '...',
                    'hash_data' => substr($hashdata, 0, 200) . '...'
                ]);
            }
            
            return $isValid;
            
        } catch (\Exception $e) {
            Log::error('VNPay validateResponse error: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'data_keys' => array_keys($inputData ?? [])
            ]);
            return false;
        }
    }

    public function getTransactionStatus($responseCode)
    {
        $statusCodes = [
            '00' => 'Giao dịch thành công',
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường)',
            '09' => 'Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng',
            '10' => 'Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
            '11' => 'Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch',
            '12' => 'Thẻ/Tài khoản của khách hàng bị khóa',
            '13' => 'Quý khách nhập sai mật khẩu xác thực giao dịch (OTP). Xin quý khách vui lòng thực hiện lại giao dịch',
            '24' => 'Khách hàng hủy giao dịch',
            '51' => 'Tài khoản của quý khách không đủ số dư để thực hiện giao dịch',
            '65' => 'Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày',
            '75' => 'Ngân hàng thanh toán đang bảo trì',
            '79' => 'KH nhập sai mật khẩu thanh toán quá số lần quy định. Xin quý khách vui lòng thực hiện lại giao dịch',
            '99' => 'Các lỗi khác (lỗi kết nối, lỗi hệ thống...)',
            '15' => 'Giao dịch đã quá thời gian chờ thanh toán'
        ];

        return $statusCodes[$responseCode] ?? 'Lỗi không xác định (Mã: ' . $responseCode . ')';
    }
}