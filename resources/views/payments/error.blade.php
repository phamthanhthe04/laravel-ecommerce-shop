@extends('layouts.app')

@section('title', 'Có lỗi xảy ra')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Có lỗi xảy ra
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Thông báo:</strong> Có vẻ như đã xảy ra lỗi kỹ thuật trong quá trình thanh toán.
                    </div>
                    
                    <p>Điều này có thể do:</p>
                    <ul>
                        <li>Lỗi kết nối mạng</li>
                        <li>Lỗi từ phía cổng thanh toán</li>
                        <li>Trình duyệt chặn JavaScript</li>
                    </ul>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('payment.index', ['order_id' => request('order_id')]) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-redo me-2"></i>Thử lại
                        </a>
                        <a href="{{ route('orders.my') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>Xem đơn hàng của tôi
                        </a>
                        <a href="{{ route('shop.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-home me-2"></i>Về trang chủ
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Thông tin hỗ trợ -->
            <div class="card mt-3">
                <div class="card-body text-center">
                    <h6>Cần hỗ trợ?</h6>
                    <p class="text-muted small">
                        Nếu vấn đề vẫn tiếp tục, vui lòng liên hệ với chúng tôi
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="text-muted">
                            <i class="fas fa-phone me-1"></i>1900-xxxx
                        </span>
                        <span class="text-muted">
                            <i class="fas fa-envelope me-1"></i>support@example.com
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto refresh nếu user ở lại trang quá lâu
setTimeout(function() {
    if (confirm('Bạn có muốn thử lại thanh toán không?')) {
        window.location.href = '{{ route("payment.index", ["order_id" => request("order_id")]) }}';
    }
}, 30000); // 30 giây
</script>
@endsection
