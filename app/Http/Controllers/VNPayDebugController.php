<?php

namespace App\Http\Controllers;

use App\Services\VNPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class VNPayDebugController extends Controller
{
    protected $vnPayService;

    public function __construct(VNPayService $vnPayService)
    {
        $this->vnPayService = $vnPayService;
    }

    /**
     * Hiển thị trang debug VNPay
     */
    public function index()
    {
        return view('debug.vnpay-debug');
    }

    /**
     * Generate test payment URL
     */
    public function generateTestUrl(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer',
                'amount' => 'required|numeric|min:1000',
                'order_info' => 'required|string'
            ]);

            $orderId = $request->order_id;
            $amount = $request->amount;
            $orderInfo = $request->order_info;
            $ipAddress = $request->ip();

            // Log request để debug
            Log::info('Generating test VNPay URL', [
                'order_id' => $orderId,
                'amount' => $amount,
                'order_info' => $orderInfo,
                'ip' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]);

            $paymentUrl = $this->vnPayService->createPaymentUrl(
                $orderId,
                $amount,
                $orderInfo,
                $ipAddress
            );

            // Parse URL để hiển thị parameters
            $parsedUrl = parse_url($paymentUrl);
            parse_str($parsedUrl['query'], $params);

            return response()->json([
                'success' => true,
                'url' => $paymentUrl,
                'parameters' => $params,
                'url_length' => strlen($paymentUrl),
                'parameter_count' => count($params)
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating test VNPay URL: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validate hash manually
     */
    public function validateHash(Request $request)
    {
        try {
            $data = $request->all();
            
            if (empty($data['vnp_SecureHash'])) {
                return response()->json([
                    'valid' => false,
                    'error' => 'vnp_SecureHash is missing'
                ]);
            }

            $receivedHash = $data['vnp_SecureHash'];
            
            // Remove hash from data để tính toán
            unset($data['vnp_SecureHash']);
            unset($data['vnp_SecureHashType']);

            // Sắp xếp parameters
            ksort($data);

            // Build hash string
            $hashData = "";
            foreach ($data as $key => $value) {
                if (!empty($value)) {
                    $hashData .= $key . "=" . $value . "&";
                }
            }
            $hashData = rtrim($hashData, '&');

            // Calculate hash
            $calculatedHash = hash_hmac('sha512', $hashData, config('vnpay.hash_secret'));
            
            $isValid = hash_equals($calculatedHash, $receivedHash);

            Log::info('Manual hash validation', [
                'is_valid' => $isValid,
                'calculated_hash' => substr($calculatedHash, 0, 20) . '...',
                'received_hash' => substr($receivedHash, 0, 20) . '...',
                'hash_data_length' => strlen($hashData)
            ]);

            return response()->json([
                'valid' => $isValid,
                'calculated_hash' => $calculatedHash,
                'received_hash' => $receivedHash,
                'hash_data' => $hashData,
                'parameters_count' => count($data)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get recent VNPay logs
     */
    public function getLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (!File::exists($logFile)) {
                return response()->json(['logs' => []]);
            }

            $logs = [];
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            // Lấy 50 dòng cuối
            $recentLines = array_slice($lines, -200);
            
            foreach ($recentLines as $line) {
                // Chỉ lấy log liên quan VNPay
                if (stripos($line, 'vnpay') !== false || stripos($line, 'payment') !== false) {
                    $logEntry = $this->parseLogLine($line);
                    if ($logEntry) {
                        $logs[] = $logEntry;
                    }
                }
            }

            // Sắp xếp theo thời gian mới nhất
            $logs = array_slice(array_reverse($logs), 0, 20);

            return response()->json(['logs' => $logs]);

        } catch (\Exception $e) {
            return response()->json([
                'logs' => [],
                'error' => 'Error reading logs: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Parse log line
     */
    private function parseLogLine($line)
    {
        // Pattern cho Laravel log format
        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.(\w+): (.+)/';
        
        if (preg_match($pattern, $line, $matches)) {
            $timestamp = $matches[1];
            $level = $matches[2];
            $message = $matches[3];
            
            // Try to extract JSON context
            $context = null;
            if (preg_match('/\{.*\}$/', $message, $jsonMatch)) {
                try {
                    $context = json_decode($jsonMatch[0], true);
                    $message = str_replace($jsonMatch[0], '', $message);
                } catch (\Exception $e) {
                    // Ignore JSON parse errors
                }
            }

            return [
                'timestamp' => $timestamp,
                'level' => $level,
                'message' => trim($message),
                'context' => $context
            ];
        }

        return null;
    }

    /**
     * Test VNPay connectivity
     */
    public function testConnectivity()
    {
        try {
            $testUrl = config('vnpay.url');
            
            // Test với cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $testUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            return response()->json([
                'success' => $httpCode === 200,
                'http_code' => $httpCode,
                'response_length' => strlen($response),
                'error' => $error ?: null,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Analyze VNPay callback data
     */
    public function analyzeCallback(Request $request)
    {
        $data = $request->all();
        
        Log::info('VNPay callback analysis', [
            'all_parameters' => $data,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);

        $analysis = [
            'parameter_count' => count($data),
            'has_secure_hash' => isset($data['vnp_SecureHash']),
            'response_code' => $data['vnp_ResponseCode'] ?? 'missing',
            'order_id' => $data['vnp_TxnRef'] ?? 'missing',
            'amount' => isset($data['vnp_Amount']) ? ($data['vnp_Amount'] / 100) : 'missing',
            'transaction_id' => $data['vnp_TransactionNo'] ?? 'missing',
            'bank_code' => $data['vnp_BankCode'] ?? 'missing',
            'validation_result' => null
        ];

        // Validate if hash is present
        if (isset($data['vnp_SecureHash'])) {
            $analysis['validation_result'] = $this->vnPayService->validateResponse($data);
        }

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'raw_data' => $data
        ]);
    }
}
