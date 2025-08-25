<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VNPaySandboxController extends Controller
{
    private $vnpTmnCode = "K83BDEI1";
    private $vnpHashSecret = "5LIRBPPT6FA7U16PEHMYGF5YP3XLUWBL";
    private $vnpUrl = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";

    /**
     * Hiển thị form thanh toán
     */
    public function showPaymentForm()
    {
        return view('vnpay.payment-form');
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000|max:100000000',
            'language' => 'required|in:vn,en',
            'bankCode' => 'nullable|string'
        ]);

        try {
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            
            $vnp_TxnRef = rand(1, 10000);
            $vnp_Amount = $request->amount;
            $vnp_Locale = $request->language;
            $vnp_BankCode = $request->bankCode ?? '';
            $vnp_IpAddr = $request->ip();
            
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
                "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef,
                "vnp_OrderType" => "other",
                "vnp_ReturnUrl" => "http://127.0.0.1:8000/vnpay-sandbox/return",
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

            Log::info('VNPay Sandbox Payment URL Created', [
                'txn_ref' => $vnp_TxnRef,
                'amount' => $vnp_Amount,
                'hash_data' => $hashdata,
                'url' => $vnp_Url_final
            ]);

            return redirect($vnp_Url_final);

        } catch (\Exception $e) {
            Log::error('VNPay createPayment error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tạo link thanh toán: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý callback từ VNPay
     */
    public function handleReturn(Request $request)
    {
        try {
            $inputData = $request->all();
            
            Log::info('VNPay Return Data', $inputData);

            // Validate hash
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
            unset($inputData['vnp_SecureHash']);
            ksort($inputData);
            
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    if ($i == 1) {
                        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                    } else {
                        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                        $i = 1;
                    }
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $this->vnpHashSecret);
            $isValidHash = ($secureHash == $vnp_SecureHash);
            
            $responseCode = $request->get('vnp_ResponseCode', '');
            $transactionStatus = $request->get('vnp_TransactionStatus', '');
            $isSuccess = ($responseCode == '00' && $transactionStatus == '00');

            $data = [
                'vnpay_data' => $request->all(),
                'is_valid_hash' => $isValidHash,
                'is_success' => $isSuccess,
                'response_code' => $responseCode,
                'transaction_status' => $transactionStatus,
                'calculated_hash' => $secureHash,
                'received_hash' => $vnp_SecureHash,
                'hash_data' => $hashData
            ];

            return view('vnpay.return-debug', $data);

        } catch (\Exception $e) {
            Log::error('VNPay handleReturn error: ' . $e->getMessage());
            return view('vnpay.return-debug', [
                'error' => $e->getMessage(),
                'vnpay_data' => $request->all()
            ]);
        }
    }
}
