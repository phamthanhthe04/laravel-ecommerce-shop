@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Chi tiết đơn hàng #{{ $order->id }}</h5>
                    <span
                        class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'confirmed' ? 'info' : ($order->status === 'shipped' ? 'primary' : ($order->status === 'delivered' ? 'success' : 'danger'))) }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <!-- Thông tin đơn hàng -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Thông tin khách hàng</h6>
                            <p class="mb-1"><strong>Tên:</strong> {{ $order->user->name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $order->user->email }}</p>
                            <p class="mb-1"><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Trạng thái thanh toán</h6>
                            <p class="mb-1">
                                <strong>Phương thức:</strong>
                                @if($order->payment_method === 'vnpay')
                                <span class="badge bg-primary">VNPay</span>
                                @elseif($order->payment_method === 'cod')
                                <span class="badge bg-warning">COD</span>
                                @elseif($order->payment_method === 'mock_card')
                                <span class="badge bg-info">Mock Card</span>
                                @elseif($order->payment_method === 'mock_quick')
                                <span class="badge bg-secondary">Mock Quick</span>
                                @else
                                <span class="badge bg-secondary">Chưa chọn</span>
                                @endif
                            </p>
                            <p class="mb-1">
                                <strong>Trạng thái:</strong>
                                <span
                                    class="badge bg-{{ $order->payment_status === 'pending' ? 'warning' : ($order->payment_status === 'paid' ? 'success' : 'danger') }}">
                                    @if($order->payment_status === 'pending')
                                    Chờ thanh toán
                                    @elseif($order->payment_status === 'paid')
                                    Đã thanh toán
                                    @elseif($order->payment_status === 'failed')
                                    Thất bại
                                    @else
                                    Đã hủy
                                    @endif
                                </span>
                            </p>
                            @if($order->payment_date)
                            <p class="mb-1"><strong>Ngày thanh toán:</strong>
                                {{ $order->payment_date->format('d/m/Y H:i') }}</p>
                            @endif
                            @if($order->vnpay_transaction_id)
                            <p class="mb-1"><strong>Mã giao dịch VNPay:</strong> {{ $order->vnpay_transaction_id }}</p>
                            @endif
                            @if($order->mock_transaction_id)
                            <p class="mb-1"><strong>Mã giao dịch Mock:</strong> {{ $order->mock_transaction_id }}</p>
                            @endif
                            @if($order->mock_card_last4)
                            <p class="mb-1"><strong>Thẻ sử dụng:</strong> ****{{ $order->mock_card_last4 }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Thông tin giao hàng -->
                    @if($order->hasDeliveryInfo())
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6><i class="fas fa-truck me-2"></i>Thông tin giao hàng</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Người nhận:</strong> {{ $order->delivery_name }}</p>
                                            <p class="mb-1"><strong>Số điện thoại:</strong> {{ $order->delivery_phone }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Địa chỉ:</strong></p>
                                            <p class="mb-1">{{ $order->getFullDeliveryAddress() }}</p>
                                            @if($order->delivery_note)
                                            <p class="mb-0"><strong>Ghi chú:</strong> {{ $order->delivery_note }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Danh sách sản phẩm -->
                    <h6>Sản phẩm đã đặt</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                        @if($item->product->description)
                                        <br><small
                                            class="text-muted">{{ Str::limit($item->product->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->price, 0, ',', '.') }}đ</td>
                                    <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3"><strong>Tổng cộng:</strong></td>
                                    <td><strong>{{ number_format($order->total, 0, ',', '.') }}đ</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4">
                        @if($order->payment_status === 'pending' && !$order->payment_method)
                        <a href="{{ route('payment.index', ['order_id' => $order->id]) }}" class="btn btn-primary">
                            <i class="fas fa-credit-card"></i> Chọn phương thức thanh toán
                        </a>
                        @endif

                        <a href="{{ route('orders.my') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        @if(Auth::user()->isAdmin())
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quản lý đơn hàng</h6>
                </div>
                <div class="card-body">
                    <!-- Cập nhật trạng thái đơn hàng -->
                    <form action="{{ route('admin.manage.orders.update', $order) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Trạng thái đơn hàng</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Chờ xử lý
                                </option>
                                <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Đã xác
                                    nhận</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Đang giao
                                </option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Đã giao
                                </option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Đã hủy
                                </option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Cập nhật trạng thái</button>
                    </form>

                    <!-- Cập nhật trạng thái thanh toán COD -->
                    @if($order->payment_method === 'cod' && $order->payment_status === 'pending')
                    <hr>
                    <h6>Thanh toán COD</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-sm" onclick="updateCodStatus('{{ $order->id }}', 'paid')">
                            <i class="fas fa-check"></i> Đã nhận tiền
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="updateCodStatus('{{ $order->id }}', 'failed')">
                            <i class="fas fa-times"></i> Không nhận được tiền
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if(Auth::user()->isAdmin())
<script>
function updateCodStatus(orderId, status) {
    if (confirm('Bạn có chắc chắn muốn cập nhật trạng thái thanh toán?')) {
        fetch(`/admin/payments/${orderId}/cod-status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    payment_status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cập nhật thành công!');
                    location.reload();
                } else {
                    alert('Có lỗi xảy ra: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật.');
            });
    }
}
</script>
@endif
@endsection