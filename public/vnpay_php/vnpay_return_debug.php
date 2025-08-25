<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VNPay Return Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
        .error { color: #dc3545; font-weight: bold; }
        .success { color: #28a745; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>VNPay Return Data Debug</h1>
    
    <div class="debug-section">
        <h2>Raw GET Data</h2>
        <pre><?php print_r($_GET); ?></pre>
    </div>

    <div class="debug-section">
        <h2>VNPay Parameters</h2>
        <table>
            <tr><th>Parameter</th><th>Value</th><th>Status</th></tr>
            <?php
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
            
            foreach ($vnpayParams as $key => $description) {
                $value = isset($_GET[$key]) ? $_GET[$key] : '';
                $status = empty($value) ? '<span class="error">Missing</span>' : '<span class="success">OK</span>';
                echo "<tr><td>$key</td><td>" . htmlspecialchars($value) . "</td><td>$status</td></tr>";
            }
            ?>
        </table>
    </div>

    <?php
    require_once("./config.php");
    
    // Validate hash
    $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
    $inputData = array();
    foreach ($_GET as $key => $value) {
        if (substr($key, 0, 4) == "vnp_") {
            $inputData[$key] = $value;
        }
    }
    
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

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    ?>

    <div class="debug-section">
        <h2>Hash Validation</h2>
        <table>
            <tr><th>Item</th><th>Value</th></tr>
            <tr><td>Hash Data</td><td><?php echo htmlspecialchars($hashData); ?></td></tr>
            <tr><td>Calculated Hash</td><td><?php echo $secureHash; ?></td></tr>
            <tr><td>Received Hash</td><td><?php echo $vnp_SecureHash; ?></td></tr>
            <tr><td>Hash Match</td><td>
                <?php 
                if ($secureHash == $vnp_SecureHash) {
                    echo '<span class="success">✓ Valid</span>';
                } else {
                    echo '<span class="error">✗ Invalid</span>';
                }
                ?>
            </td></tr>
        </table>
    </div>

    <div class="debug-section">
        <h2>Transaction Result</h2>
        <?php
        $responseCode = $_GET['vnp_ResponseCode'] ?? '';
        $transactionStatus = $_GET['vnp_TransactionStatus'] ?? '';
        
        echo "<p><strong>Response Code:</strong> $responseCode</p>";
        echo "<p><strong>Transaction Status:</strong> $transactionStatus</p>";
        
        if ($secureHash == $vnp_SecureHash) {
            if ($responseCode == '00') {
                echo '<p class="success">✓ Giao dịch thành công</p>';
            } else {
                echo '<p class="error">✗ Giao dịch thất bại - Mã lỗi: ' . $responseCode . '</p>';
                
                // VNPay response codes
                $responseCodes = [
                    '00' => 'Giao dịch thành công',
                    '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
                    '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
                    '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
                    '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.',
                    '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa.',
                    '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP).',
                    '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
                    '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
                    '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
                    '75' => 'Ngân hàng thanh toán đang bảo trì.',
                    '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định.',
                    '99' => 'Các lỗi khác (lỗi còn lại, không có trong danh sách mã lỗi đã liệt kê)'
                ];
                
                if (isset($responseCodes[$responseCode])) {
                    echo '<p class="warning">Mô tả: ' . $responseCodes[$responseCode] . '</p>';
                }
            }
        } else {
            echo '<p class="error">✗ Chữ ký không hợp lệ - Dữ liệu có thể đã bị thay đổi</p>';
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>Actions</h2>
        <p><a href="vnpay_pay.php" style="background: #007bff; color: white; padding: 10px 15px; text-decoration: none;">Thực hiện giao dịch mới</a></p>
        <p><a href="test_form.php" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none;">Form test debug</a></p>
    </div>
</body>
</html>
