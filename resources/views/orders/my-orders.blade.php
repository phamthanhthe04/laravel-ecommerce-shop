@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-list me-2"></i>Đơn hàng của tôi</h2>
                <a href="{{ route('shop.index') }}" class="btn btn-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                </a>
            </div>

            @if($orders->count() > 0)
                <div class="row">
                    @foreach($orders as $order)
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">Đơn hàng #{{ $order->id }}</h5>
                                        <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'info') }} fs-6">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }} fs-6 ms-1">
                                            @if($order->payment_status === 'pending')
                                                Chờ thanh toán
                                            @elseif($order->payment_status === 'paid')
                                                Đã thanh toán
                                            @elseif($order->payment_status === 'failed')
                                                Thanh toán thất bại
                                            @else
                                                Đã hủy
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6>Sản phẩm:</h6>
                                            @foreach($order->orderItems as $item)
                                                <div class="d-flex align-items-center mb-2">
                                                    <img src="{{ $item->product->image ?? 'https://via.placeholder.com/50x50' }}" 
                                                         class="rounded me-3" 
                                                         style="width: 50px; height: 50px; object-fit: cover;" 
                                                         alt="{{ $item->product->name }}">
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold">{{ $item->product->name }}</div>
                                                        <small class="text-muted">
                                                            Số lượng: {{ $item->quantity }} × 
                                                            {{ number_format($item->price, 0, ',', '.') }}đ
                                                        </small>
                                                    </div>
                                                    <div class="fw-bold text-primary">
                                                        {{ number_format($item->quantity * $item->price, 0, ',', '.') }}đ
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="bg-light p-3 rounded">
                                                <h6>Thông tin đơn hàng</h6>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Tổng cộng:</span>
                                                    <span class="fw-bold text-primary">
                                                        {{ number_format($order->total, 0, ',', '.') }}đ
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Phương thức thanh toán:</span>
                                                    @if($order->payment_method === 'vnpay')
                                                        <span class="badge bg-primary">VNPay</span>
                                                    @elseif($order->payment_method === 'cod')
                                                        <span class="badge bg-warning">COD</span>
                                                    @else
                                                        <span class="badge bg-secondary">Chưa chọn</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Trạng thái:</span>
                                                    <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'info') }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-3">
                                                    <span>Ngày đặt:</span>
                                                    <span>{{ $order->created_at->format('d/m/Y') }}</span>
                                                </div>
                                                
                                                <div class="d-grid gap-2">
                                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i> Xem chi tiết
                                                    </a>
                                                    @if($order->payment_status === 'pending' && !$order->payment_method)
                                                    <a href="{{ route('payment.index', ['order_id' => $order->id]) }}" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-credit-card"></i> Thanh toán
                                                    </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h4>Bạn chưa có đơn hàng nào</h4>
                    <p class="text-muted">Hãy khám phá các sản phẩm tuyệt vời và đặt hàng ngay!</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Bắt đầu mua sắm
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
