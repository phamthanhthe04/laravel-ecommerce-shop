<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNPay Return Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .debug-section { margin-bottom: 20px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>VNPay Return Debug Information</h1>

        @if(isset($error))
            <div class="alert alert-danger">
                <strong>Error:</strong> {{ $error }}
            </div>
        @endif

        <div class="debug-section">
            <div class="card">
                <div class="card-header">
                    <h3>Transaction Summary</h3>
                </div>
                <div class="card-body">
                    @if(isset($is_valid_hash) && $is_valid_hash)
                        @if(isset($is_success) && $is_success)
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> <strong>Giao dịch thành công!</strong>
                            </div>
                        @else
                            <div class="alert alert-danger">
                                <i class="bi bi-x-circle"></i> <strong>Giao dịch thất bại!</strong>
                                <br>Mã lỗi: {{ $response_code ?? 'N/A' }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-danger">
                            <i class="bi bi-shield-x"></i> <strong>Chữ ký không hợp lệ - Dữ liệu có thể đã bị thay đổi!</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="debug-section">
            <div class="card">
                <div class="card-header">
                    <h3>VNPay Response Data</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $vnpayParams = [
                                    'vnp_TxnRef' => 'Mã đơn hàng',
                                    'vnp_Amount' => 'Số tiền (cent)',
                                    'vnp_OrderInfo' => 'Thông tin đơn hàng',
                                    'vnp_ResponseCode' => 'Mã phản hồi',
                                    'vnp_TransactionNo' => 'Mã GD tại VNPay',
                                    'vnp_BankCode' => 'Mã ngân hàng',
                                    'vnp_PayDate' => 'Ngày thanh toán',
                                    'vnp_SecureHash' => 'Chữ ký bảo mật',
                                    'vnp_TransactionStatus' => 'Trạng thái GD'
                                ];
                            @endphp
                            
                            @foreach($vnpayParams as $key => $description)
                                @php
                                    $value = isset($vnpay_data[$key]) ? $vnpay_data[$key] : '';
                                    $status = empty($value) ? '<span class="error">Missing</span>' : '<span class="success">OK</span>';
                                @endphp
                                <tr>
                                    <td>{{ $key }}</td>
                                    <td>{{ $value }}</td>
                                    <td>{!! $status !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="debug-section">
            <div class="card">
                <div class="card-header">
                    <h3>Hash Validation</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <td><strong>Hash Data:</strong></td>
                            <td><code>{{ $hash_data ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Calculated Hash:</strong></td>
                            <td><code>{{ $calculated_hash ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Received Hash:</strong></td>
                            <td><code>{{ $received_hash ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Hash Match:</strong></td>
                            <td>
                                @if(isset($is_valid_hash))
                                    @if($is_valid_hash)
                                        <span class="success">✓ Valid</span>
                                    @else
                                        <span class="error">✗ Invalid</span>
                                    @endif
                                @else
                                    <span class="warning">Unknown</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="debug-section">
            <div class="card">
                <div class="card-header">
                    <h3>Raw Response Data</h3>
                </div>
                <div class="card-body">
                    <pre>{{ json_encode($vnpay_data ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>

        <div class="debug-section">
            <div class="card">
                <div class="card-header">
                    <h3>Response Code Information</h3>
                </div>
                <div class="card-body">
                    @php
                        $responseCodes = [
                            '00' => 'Giao dịch thành công',
                            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
                            '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
                            '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
                            '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán.',
                            '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa.',
                            '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP).',
                            '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
                            '51' => 'Giao dịch không thành công do: Tài khoản không đủ số dư.',
                            '65' => 'Giao dịch không thành công do: Tài khoản đã vượt quá hạn mức giao dịch trong ngày.',
                            '75' => 'Ngân hàng thanh toán đang bảo trì.',
                            '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định.',
                            '99' => 'Các lỗi khác'
                        ];
                        $currentCode = $response_code ?? '';
                    @endphp
                    
                    <p><strong>Current Response Code:</strong> {{ $currentCode }}</p>
                    @if(isset($responseCodes[$currentCode]))
                        <p><strong>Description:</strong> {{ $responseCodes[$currentCode] }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="debug-section">
            <a href="{{ route('vnpay.sandbox.form') }}" class="btn btn-primary">Thực hiện giao dịch mới</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Về trang chủ</a>
        </div>
    </div>
</body>
</html>
