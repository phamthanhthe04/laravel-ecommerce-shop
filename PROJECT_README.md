# Laravel E-commerce Shop

Dự án thương mại điện tử được xây dựng bằng Laravel với tích hợp thanh toán VNPay.

## Tính năng chính

### 🛍️ Mua sắm

-   **Hiển thị sản phẩm** với danh mục, giá, khuyến mãi
-   **Giỏ hàng** với chức năng thêm, sửa, xóa sản phẩm
-   **Thanh toán** với địa chỉ giao hàng chi tiết
-   **Quản lý đơn hàng** cho khách hàng

### 💳 Thanh toán

-   **VNPay Integration** - Thanh toán online qua VNPay
-   **COD (Cash on Delivery)** - Thanh toán khi nhận hàng
-   **Mock Payment** - Hệ thống thanh toán giả lập để test

### 🎯 Khuyến mãi

-   **Khuyến mãi theo phần trăm** (%)
-   **Khuyến mãi theo số tiền** cố định
-   **Quản lý thời gian** khuyến mãi
-   **Hiển thị giá gốc và giá sau khuyến mãi**

### 👨‍💼 Quản trị

-   **Dashboard** với thống kê doanh thu
-   **Quản lý sản phẩm** (CRUD)
-   **Quản lý danh mục** (CRUD)
-   **Quản lý đơn hàng** với cập nhật trạng thái
-   **Quản lý khuyến mãi** (CRUD)
-   **Quản lý người dùng**

### 🔧 Tính năng kỹ thuật

-   **Authentication** - Đăng nhập, đăng ký
-   **Authorization** - Phân quyền Admin/User
-   **Session Management** - Quản lý giỏ hàng qua session
-   **Inventory Management** - Quản lý tồn kho
-   **Logging** - Ghi log chi tiết các giao dịch
-   **Responsive Design** - Giao diện responsive

## Cài đặt

### Yêu cầu hệ thống

-   PHP >= 8.1
-   Composer
-   MySQL/SQLite
-   Laravel 11.x

### Các bước cài đặt

1. **Clone repository**

```bash
git clone <repository-url>
cd laravel-shop
```

2. **Cài đặt dependencies**

```bash
composer install
npm install
```

3. **Cấu hình environment**

```bash
cp .env.example .env
php artisan key:generate
```

4. **Cấu hình database**
   Chỉnh sửa file `.env`:

```
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

5. **Chạy migration và seeder**

```bash
php artisan migrate
php artisan db:seed
```

6. **Cấu hình VNPay** (tùy chọn)
   Chỉnh sửa file `.env`:

```
VNPAY_TMN_CODE=your_tmn_code
VNPAY_HASH_SECRET=your_hash_secret
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
```

7. **Khởi chạy server**

```bash
php artisan serve
```

## Sử dụng

### Tài khoản mặc định

-   **Admin**: admin@example.com / password
-   **User**: user@example.com / password

### Workflow cơ bản

1. **Khách hàng**: Duyệt sản phẩm → Thêm vào giỏ hàng → Nhập thông tin giao hàng → Chọn phương thức thanh toán
2. **Admin**: Quản lý sản phẩm/đơn hàng → Cập nhật trạng thái đơn hàng → Xác nhận thanh toán COD

## Cấu trúc dự án

```
app/
├── Http/Controllers/
│   ├── AdminController.php         # Quản trị admin
│   ├── CartController.php          # Giỏ hàng
│   ├── PaymentController.php       # Thanh toán
│   ├── ShopController.php          # Shop frontend
│   └── VNPay*.php                  # VNPay integration
├── Models/
│   ├── User.php                    # Người dùng
│   ├── Product.php                 # Sản phẩm
│   ├── Order.php                   # Đơn hàng
│   ├── Category.php                # Danh mục
│   └── Promotion.php               # Khuyến mãi
└── Services/
    ├── VNPayService.php            # VNPay service
    └── InventoryService.php        # Quản lý tồn kho
```

## API Documentation

### Payment Flow

1. **Tạo đơn hàng**: `POST /cart/checkout`
2. **Chọn thanh toán VNPay**: `POST /payment/vnpay`
3. **Callback từ VNPay**: `GET /vnpay/return`
4. **Thanh toán COD**: `POST /payment/cod`

### Admin Routes

-   `GET /admin/dashboard` - Trang tổng quan
-   `GET /admin/orders` - Quản lý đơn hàng
-   `PUT /admin/payments/{order}/cod-status` - Cập nhật trạng thái COD

## Troubleshooting

### Lỗi thường gặp

1. **Method not allowed khi checkout**

    - Kiểm tra form method phải là POST
    - Đảm bảo không có nested forms

2. **VNPay payment failed**

    - Kiểm tra cấu hình VNPay trong `.env`
    - Kiểm tra logs tại `storage/logs/laravel.log`

3. **Permission denied**
    - Chạy `chmod -R 755 storage bootstrap/cache`

## Contributing

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Tạo Pull Request

## License

Dự án này sử dụng [MIT License](LICENSE).

## Contact

-   **Email**: shop@example.com
-   **GitHub**: [Repository Link]

---

**Phiên bản**: 1.0.0  
**Cập nhật lần cuối**: August 2025
