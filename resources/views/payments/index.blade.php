@extends('layouts.app')

@section('title', 'Chọn phương thức thanh toán')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Chọn phương thức thanh toán</h5>
                </div>
                <div class="card-body">
                    <!-- Thông tin đơn hàng -->
                    <div class="order-summary mb-4">
                        <h6>Thông tin đơn hàng #{{ $order->id }}</h6>
                        <div class="alert alert-info">
                            <strong>Người mua:</strong> {{ $order->user->name }} ({{ $order->user->email }})
                            <br>
                            <strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                        
                        <!-- Thông tin giao hàng -->
                        @if($order->hasDeliveryInfo())
                        <div class="alert alert-secondary">
                            <h6><i class="fas fa-truck me-2"></i>Thông tin giao hàng</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Người nhận:</strong> {{ $order->delivery_name }}<br>
                                    <strong>Số điện thoại:</strong> {{ $order->delivery_phone }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Địa chỉ:</strong> {{ $order->getFullDeliveryAddress() }}
                                    @if($order->delivery_note)
                                    <br><strong>Ghi chú:</strong> {{ $order->delivery_note }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Giá gốc</th>
                                        <th>Khuyến mãi</th>
                                        <th>Giá bán</th>
                                        <th>Số lượng</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalOriginal = 0; $totalSaved = 0; @endphp
                                    @foreach($order->orderItems as $item)
                                    @php
                                        $product = $item->product;
                                        $activePromotion = $product->getBestActivePromotion();
                                        $originalPrice = $product->price;
                                        $sellingPrice = $item->price; // Giá đã có khuyến mãi được lưu
                                        $saved = ($originalPrice - $sellingPrice) * $item->quantity;
                                        $totalOriginal += $originalPrice * $item->quantity;
                                        $totalSaved += $saved;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                            @if($activePromotion)
                                                <br><small class="text-success">
                                                    <i class="fas fa-gift"></i> {{ $activePromotion->name }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="{{ $sellingPrice < $originalPrice ? 'text-muted text-decoration-line-through' : '' }}">
                                                {{ number_format($originalPrice, 0, ',', '.') }}đ
                                            </span>
                                        </td>
                                        <td>
                                            @if($activePromotion)
                                                @if($activePromotion->discount_type === 'percentage')
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-percentage me-1"></i>-{{ $activePromotion->discount_value }}%
                                                    </span>
                                                @else
                                                    <span class="badge bg-info text-white">
                                                        <i class="fas fa-minus-circle me-1"></i>-{{ number_format($activePromotion->discount_value, 0, ',', '.') }}đ
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sellingPrice < $originalPrice)
                                                <span class="text-danger fw-bold">{{ number_format($sellingPrice, 0, ',', '.') }}đ</span>
                                            @else
                                                {{ number_format($sellingPrice, 0, ',', '.') }}đ
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            <strong>{{ number_format($sellingPrice * $item->quantity, 0, ',', '.') }}đ</strong>
                                            @if($saved > 0)
                                                <br><small class="text-success">Tiết kiệm: {{ number_format($saved, 0, ',', '.') }}đ</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    @if($totalSaved > 0)
                                    <tr class="table-warning">
                                        <td colspan="5"><em>Tổng giá gốc:</em></td>
                                        <td><em class="text-muted text-decoration-line-through">{{ number_format($totalOriginal, 0, ',', '.') }}đ</em></td>
                                    </tr>
                                    <tr class="table-success">
                                        <td colspan="5"><em>Tổng tiết kiệm:</em></td>
                                        <td><em class="text-success">-{{ number_format($totalSaved, 0, ',', '.') }}đ</em></td>
                                    </tr>
                                    @endif
                                    <tr class="table-info">
                                        <td colspan="5"><strong>Tổng thanh toán:</strong></td>
                                        <td><strong class="text-primary fs-5">{{ number_format($order->total, 0, ',', '.') }}đ</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Phương thức thanh toán -->
                    <div class="payment-methods">
                        <h6>Chọn phương thức thanh toán:</h6>
                        
                        <!-- VNPay -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Thanh toán online qua VNPay</h6>
                                        <small class="text-muted">Thanh toán ngay bằng thẻ ATM, thẻ tín dụng, QR Code</small>
                                    </div>
                                    <form action="{{ route('payment.vnpay') }}" method="POST" class="ms-3" id="vnpayForm">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                        <button type="submit" class="btn btn-primary" id="vnpayBtn">
                                            <i class="fas fa-credit-card"></i> Thanh toán ngay
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Mock Payment -->
                        <div class="card mb-3 border-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            Mock Payment Gateway 
                                            <span class="badge bg-warning text-dark">TEST</span>
                                        </h6>
                                        <small class="text-muted">Thanh toán giả lập để test hệ thống</small>
                                    </div>
                                    <a href="{{ route('mock.payment.form', $order->id) }}" class="btn btn-outline-warning ms-3">
                                        <i class="fas fa-flask"></i> Test Payment
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- COD -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Thanh toán khi nhận hàng (COD)</h6>
                                        <small class="text-muted">Thanh toán bằng tiền mặt khi nhận hàng</small>
                                    </div>
                                    <form action="{{ route('payment.cod') }}" method="POST" class="ms-3" id="codForm">
                                        @csrf
                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                        <button type="submit" class="btn btn-success" id="codBtn">
                                            <i class="fas fa-money-bill-wave"></i> Chọn COD
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Mock Payment -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Mock Payment (Test)</h6>
                                        <small class="text-muted">Thanh toán giả lập cho mục đích testing</small>
                                    </div>
                                    <a href="{{ route('mock.payment.form', $order->id) }}" class="btn btn-warning ms-3">
                                        <i class="fas fa-vial"></i> Test Payment
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('orders.my') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại đơn hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // VNPay form submit
    const vnpayForm = document.getElementById('vnpayForm');
    const vnpayBtn = document.getElementById('vnpayBtn');
    
    if (vnpayForm && vnpayBtn) {
        vnpayForm.addEventListener('submit', function(e) {
            // Disable button và show loading
            vnpayBtn.disabled = true;
            vnpayBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            
            // Re-enable sau 10 giây nếu có lỗi
            setTimeout(function() {
                vnpayBtn.disabled = false;
                vnpayBtn.innerHTML = '<i class="fas fa-credit-card"></i> Thanh toán ngay';
            }, 10000);
        });
    }
    
    // COD form submit
    const codForm = document.getElementById('codForm');
    const codBtn = document.getElementById('codBtn');
    
    if (codForm && codBtn) {
        codForm.addEventListener('submit', function(e) {
            // Disable button và show loading
            codBtn.disabled = true;
            codBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            
            // Re-enable sau 5 giây nếu có lỗi
            setTimeout(function() {
                codBtn.disabled = false;
                codBtn.innerHTML = '<i class="fas fa-money-bill-wave"></i> Chọn COD';
            }, 5000);
        });
    }
});

// Xử lý lỗi timer từ VNPay (nếu có)
window.addEventListener('error', function(e) {
    if (e.message && e.message.includes('timer is not defined')) {
        console.log('VNPay timer error detected and handled');
        // Ngăn chặn lỗi hiển thị trên console
        e.preventDefault();
        return true;
    }
});

// Backup xử lý cho các reference error khác
window.addEventListener('unhandledrejection', function(e) {
    console.log('Unhandled promise rejection:', e.reason);
    // Ngăn chặn uncaught promise rejection
    e.preventDefault();
});
</script>
@endsection
@endsection