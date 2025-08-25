<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Ho_Chi_Minh');

/**
 * Debug version of vnpay_create_payment.php
 * @author CTT VNPAY
 */

echo "<h2>VNPay Create Payment Debug</h2>";

require_once("./config.php");

echo "<h3>POST Data Received:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

if (empty($_POST)) {
    echo "<p style='color: red;'>ERROR: No POST data received!</p>";
    echo "<p><a href='vnpay_pay.php'>Go back to payment form</a></p>";
    exit;
}

$vnp_TxnRef = rand(1,10000); //Mã giao dịch thanh toán tham chiếu của merchant
$vnp_Amount = $_POST['amount'] ?? 0; // Số tiền thanh toán
$vnp_Locale = $_POST['language'] ?? 'vn'; //Ngôn ngữ chuyển hướng thanh toán
$vnp_BankCode = $_POST['bankCode'] ?? ''; //Mã phương thức thanh toán
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; //IP Khách hàng thanh toán

echo "<h3>Processed Data:</h3>";
echo "TxnRef: $vnp_TxnRef<br>";
echo "Amount: $vnp_Amount<br>";
echo "Locale: $vnp_Locale<br>";
echo "BankCode: $vnp_BankCode<br>";
echo "IP: $vnp_IpAddr<br>";

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount * 100,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef,
    "vnp_OrderType" => "other",
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $expire
);

if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

echo "<h3>Input Data Array:</h3>";
echo "<pre>";
print_r($inputData);
echo "</pre>";

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

echo "<h3>Hash Data:</h3>";
echo "<textarea style='width:100%; height:100px;'>$hashdata</textarea><br>";

$vnp_Url_final = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret) && !empty($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url_final .= 'vnp_SecureHash=' . $vnpSecureHash;
    
    echo "<h3>Generated Secure Hash:</h3>";
    echo "<textarea style='width:100%; height:60px;'>$vnpSecureHash</textarea><br>";
} else {
    echo "<h3 style='color: red;'>ERROR: vnp_HashSecret is empty!</h3>";
}

echo "<h3>Final Payment URL:</h3>";
echo "<textarea style='width:100%; height:150px;'>$vnp_Url_final</textarea><br>";

echo "<h3>Actions:</h3>";
echo "<p><a href='$vnp_Url_final' target='_blank' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none;'>Test Payment URL</a></p>";
echo "<p><a href='vnpay_pay.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none;'>Back to Payment Form</a></p>";

echo "<h3>Auto Redirect in 10 seconds:</h3>";
echo "<p>You will be redirected automatically, or click the test link above.</p>";
echo "<script>
setTimeout(function() {
    window.location.href = '$vnp_Url_final';
}, 10000);
</script>";
?>
