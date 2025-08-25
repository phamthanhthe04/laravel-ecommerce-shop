@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@section('content')
<h2 class="mb-4">Quản lý đơn hàng</h2>

<!-- Orders Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-shopping-cart me-2"></i>Danh sách đơn hàng</h5>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Thông tin giao hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thanh toán</th>
                                        <th>Phương thức</th>
                                        <th>Ngày đặt</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td><strong>#{{ $order->id }}</strong></td>
                                            <td>
                                                <div>
                                                    <strong>{{ $order->user->name }}</strong><br>
                                                    <small class="text-muted">{{ $order->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($order->hasDeliveryInfo())
                                                    <div>
                                                        <strong>{{ $order->delivery_name }}</strong><br>
                                                        <small class="text-muted">{{ $order->delivery_phone }}</small><br>
                                                        <small class="text-muted">{{ Str::limit($order->getFullDeliveryAddress(), 50) }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Chưa có</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($order->total, 0, ',', '.') }}đ</td>
                                            <td>
                                                <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : ($order->status == 'processing' ? 'info' : ($order->status == 'confirmed' ? 'primary' : ($order->status == 'shipping' ? 'info' : 'danger')))) }}">
                                                    @if($order->status === 'pending')
                                                        Chờ xử lý
                                                    @elseif($order->status === 'confirmed')
                                                        Đã xác nhận
                                                    @elseif($order->status === 'processing')
                                                        Đang xử lý
                                                    @elseif($order->status === 'shipping')
                                                        Đang giao
                                                    @elseif($order->status === 'completed')
                                                        Đã giao
                                                    @elseif($order->status === 'cancelled')
                                                        Đã hủy
                                                    @else
                                                        {{ ucfirst($order->status) }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'pending' ? 'warning' : 'danger') }}">
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
                                            </td>
                                            <td>
                                                @if($order->payment_method === 'vnpay')
                                                    <span class="badge bg-primary">VNPay</span>
                                                @elseif($order->payment_method === 'cod')
                                                    <span class="badge bg-warning">COD</span>
                                                    @if($order->payment_status === 'pending')
                                                        <div class="mt-1">
                                                            <button class="btn btn-success btn-xs" onclick="updateCodStatus(@json($order->id), 'paid')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button class="btn btn-danger btn-xs" onclick="updateCodStatus(@json($order->id), 'failed')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">Chưa chọn</span>
                                                @endif
                                            </td>
                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('admin.manage.orders.show', $order) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form method="POST" action="{{ route('admin.manage.orders.update', $order) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="status" value="pending">
                                                                <button type="submit" class="dropdown-item">
                                                                    <span class="badge bg-warning me-2">Chờ xử lý</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="{{ route('admin.manage.orders.update', $order) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="status" value="confirmed">
                                                                <button type="submit" class="dropdown-item">
                                                                    <span class="badge bg-primary me-2">Đã xác nhận</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="{{ route('admin.manage.orders.update', $order) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="status" value="processing">
                                                                <button type="submit" class="dropdown-item">
                                                                    <span class="badge bg-info me-2">Đang xử lý</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="{{ route('admin.manage.orders.update', $order) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="status" value="shipping">
                                                                <button type="submit" class="dropdown-item">
                                                                    <span class="badge bg-info me-2">Đang giao</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="{{ route('admin.manage.orders.update', $order) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="dropdown-item">
                                                                    <span class="badge bg-success me-2">Đã giao</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="{{ route('admin.manage.orders.update', $order) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="status" value="cancelled">
                                                                <button type="submit" class="dropdown-item">
                                                                    <span class="badge bg-danger me-2">Đã hủy</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Chưa có đơn hàng nào</p>
                        </div>
                    @endif
                </div>
            </div>

<script>
function updateCodStatus(orderId, status) {
    if (confirm('Bạn có chắc chắn muốn cập nhật trạng thái thanh toán?')) {
        fetch(`/payments/${orderId}/cod-status`, {
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
@endsection
