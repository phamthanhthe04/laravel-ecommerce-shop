<?php
/**
 * VNPay Laravel Integration
 * File này tích hợp files sandbox VNPay với dự án Laravel
 */

class VNPaySandboxIntegration
{
    private $vnpTmnCode;
    private $vnpHashSecret;
    private $vnpUrl;
    private $vnpReturnUrl;
    private $vnpApiUrl;

    public function __construct()
    {
        // Sử dụng config từ Laravel hoặc từ file config.php
        $this->vnpTmnCode = "K83BDEI1";
        $this->vnpHashSecret = "5LIRBPPT6FA7U16PEHMYGF5YP3XLUWBL";
        $this->vnpUrl = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $this->vnpReturnUrl = "http://127.0.0.1:8000/vnpay/return";
        $this->vnpApiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPaymentUrl($orderId, $amount, $orderInfo = null, $locale = 'vn', $bankCode = '', $ipAddr = null)
    {
        // Set timezone
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        
        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));
        
        if (!$ipAddr) {
            $ipAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }
        
        if (!$orderInfo) {
            $orderInfo = "Thanh toan GD:" . $orderId;
        }

        $inputData = array(
            "vnp_Version" => "2.1.0",
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
            "vnp_TxnRef" => $orderId,
            "vnp_ExpireDate" => $expire
        );

        if (isset($bankCode) && $bankCode != "") {
            $inputData['vnp_BankCode'] = $bankCode;
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

        $vnp_Url = $this->vnpUrl . "?" . $query;
        if (isset($this->vnpHashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnpHashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        
        return $vnp_Url;
    }

    /**
     * Xác thực phản hồi từ VNPay
     */
    public function validateResponse($inputData)
    {
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
        return ($secureHash == $vnp_SecureHash);
    }

    /**
     * Xử lý IPN (Instant Payment Notification)
     */
    public function processIPN($inputData)
    {
        $returnData = array();
        
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
        $vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
        $vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
        $vnp_Amount = $inputData['vnp_Amount']/100; // Số tiền thanh toán VNPAY phản hồi
        $orderId = $inputData['vnp_TxnRef'];

        try {
            // Kiểm tra checksum của dữ liệu
            if ($secureHash == $vnp_SecureHash) {
                // Ở đây bạn cần kiểm tra đơn hàng trong database Laravel
                // và cập nhật trạng thái đơn hàng
                
                // Giả sử kiểm tra đơn hàng thành công
                if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
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
        } catch (Exception $e) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknown error';
        }

        return $returnData;
    }
}
