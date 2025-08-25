<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Ho_Chi_Minh');

echo "<h2>VNPay Debug Test</h2>";

// Kiểm tra config
require_once("./config.php");

echo "<h3>Config Information:</h3>";
echo "vnp_TmnCode: " . $vnp_TmnCode . "<br>";
echo "vnp_HashSecret: " . (empty($vnp_HashSecret) ? "EMPTY!" : "SET (length: " . strlen($vnp_HashSecret) . ")") . "<br>";
echo "vnp_Url: " . $vnp_Url . "<br>";
echo "vnp_Returnurl: " . $vnp_Returnurl . "<br>";

// Test data
$test_data = array(
    'amount' => '10000',
    'language' => 'vn',
    'bankCode' => ''
);

echo "<h3>Test Payment URL Generation:</h3>";

$vnp_TxnRef = rand(1,10000);
$vnp_Amount = $test_data['amount'];
$vnp_Locale = $test_data['language'];
$vnp_BankCode = $test_data['bankCode'];
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

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

echo "<h4>Input Data:</h4>";
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

echo "<h4>Hash Data:</h4>";
echo "<textarea style='width:100%; height:100px;'>" . $hashdata . "</textarea><br>";

$vnp_Url_final = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret) && !empty($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url_final .= 'vnp_SecureHash=' . $vnpSecureHash;
    
    echo "<h4>Generated Hash:</h4>";
    echo "<textarea style='width:100%; height:60px;'>" . $vnpSecureHash . "</textarea><br>";
} else {
    echo "<h4>ERROR: vnp_HashSecret is empty!</h4>";
}

echo "<h4>Final Payment URL:</h4>";
echo "<textarea style='width:100%; height:100px;'>" . $vnp_Url_final . "</textarea><br>";

echo "<h4>Test Link:</h4>";
echo "<a href='" . $vnp_Url_final . "' target='_blank'>Click here to test payment</a><br>";

echo "<hr>";
echo "<h3>POST Data Debug (if form submitted):</h3>";
if (!empty($_POST)) {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} else {
    echo "No POST data received.";
}
?>
