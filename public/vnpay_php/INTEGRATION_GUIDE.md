# Hướng dẫn Tích hợp VNPay Sandbox với Laravel

## 1. Thông tin đã cấu hình trong files VNPay Sandbox

### Files đã được cập nhật:

-   **config.php**: Đã cập nhật thông tin TMN_CODE và HASH_SECRET từ dự án Laravel
-   **vnpay_create_payment.php**: Đã sửa lỗi syntax PHP (dùng `.` thay vì `+` để nối chuỗi)
-   **vnpay_laravel_integration.php**: File tích hợp mới để sử dụng trong Laravel

### Thông tin cấu hình hiện tại:

```php
$vnp_TmnCode = "K83BDEI1";
$vnp_HashSecret = "5LIRBPPT6FA7U16PEHMYGF5YP3XLUWBL";
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl = "http://127.0.0.1:8000/vnpay/return";
```

## 2. So sánh với dự án Laravel hiện tại

### Ưu điểm của files sandbox:

1. **Code đơn giản hơn**: Logic xử lý hash và URL rõ ràng
2. **Tuân thủ chuẩn VNPay**: Cách tính hash theo đúng tài liệu VNPay
3. **Xử lý IPN hoàn chỉnh**: File vnpay_ipn.php xử lý thông báo từ VNPay

### Điểm khác biệt chính:

1. **Cách tính hash**: Sandbox dùng cách đơn giản hơn, ít phức tạp
2. **Xử lý URL encoding**: Rõ ràng khi nào cần encode, khi nào không
3. **Validation**: Logic validation đơn giản và hiệu quả

## 3. Cách tích hợp vào dự án Laravel

### Option 1: Thay thế VNPayService hiện tại

```php
// Trong PaymentController, thay vì dùng VNPayService
use VNPaySandboxIntegration;

$vnpayService = new VNPaySandboxIntegration();
$paymentUrl = $vnpayService->createPaymentUrl($orderId, $amount, $orderInfo);
```

### Option 2: Tạo VNPayService mới dựa trên sandbox

Tạo `app/Services/VNPaySandboxService.php` với logic từ files sandbox

### Option 3: Cập nhật logic hash trong VNPayServiceFixed

Áp dụng cách tính hash từ sandbox vào service hiện tại

## 4. Các file cần thay đổi trong Laravel

### 4.1 Cập nhật config/vnpay.php

```php
return [
    'vnp_url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'vnp_returnurl' => env('VNPAY_RETURN_URL', 'http://127.0.0.1:8000/vnpay/return'),
    'vnp_tmncode' => env('VNPAY_TMN_CODE', 'K83BDEI1'),
    'vnp_hashsecret' => env('VNPAY_HASH_SECRET', '5LIRBPPT6FA7U16PEHMYGF5YP3XLUWBL'),
    'vnp_version' => env('VNPAY_VERSION', '2.1.0'),
    'vnp_apiurl' => env('VNPAY_API_URL', 'http://sandbox.vnpayment.vn/merchant_webapi/merchant.html'),
];
```

### 4.2 Cập nhật .env

```env
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://127.0.0.1:8000/vnpay/return
VNPAY_TMN_CODE=K83BDEI1
VNPAY_HASH_SECRET=5LIRBPPT6FA7U16PEHMYGF5YP3XLUWBL
VNPAY_VERSION=2.1.0
```

### 4.3 Thêm route cho IPN

```php
// routes/web.php
Route::post('/vnpay/ipn', [PaymentController::class, 'vnpayIPN'])->name('vnpay.ipn');
```

## 5. Cách test thanh toán

### 5.1 Sử dụng files sandbox trực tiếp:

1. Truy cập: `http://localhost/vnpay_php/vnpay_pay.php`
2. Nhập số tiền test
3. Chọn phương thức thanh toán
4. Kiểm tra kết quả

### 5.2 Thông tin test VNPay Sandbox:

-   **Thẻ test**: 9704198526191432198
-   **Tên chủ thẻ**: NGUYEN VAN A
-   **Ngày hết hạn**: 07/15
-   **Mật khẩu OTP**: 123456

## 6. Lưu ý quan trọng

### 6.1 Security:

-   Luôn validate hash từ VNPay
-   Không tin tưởng dữ liệu từ frontend
-   Xử lý IPN để cập nhật trạng thái đơn hàng

### 6.2 Production:

-   Thay đổi URL sang production: `https://vnpayment.vn/paymentv2/vpcpay.html`
-   Sử dụng TMN_CODE và HASH_SECRET thật từ VNPay
-   Setup SSL cho domain

### 6.3 Debugging:

-   Kiểm tra log trong Laravel
-   So sánh hash data giữa request và response
-   Verify timezone setting (Asia/Ho_Chi_Minh)

## 7. Khuyến nghị

**Nên sử dụng logic từ files sandbox** vì:

1. Đơn giản và dễ maintain
2. Theo đúng tài liệu VNPay
3. Đã được test bởi VNPay team
4. Ít lỗi hơn so với tự implement

**Bước tiếp theo:**

1. Test files sandbox trước
2. Tích hợp vào Laravel theo Option 2
3. Test với dữ liệu thật từ dự án
4. Deploy và test end-to-end
