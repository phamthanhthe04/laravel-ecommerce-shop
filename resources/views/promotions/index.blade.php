@extends('layouts.admin')

@section('title', 'Quản lý khuyến mãi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý khuyến mãi</h2>
    <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Thêm khuyến mãi
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-gift me-2"></i>Danh sách khuyến mãi</h5>
    </div>
    <div class="card-body">
        @if($promotions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên khuyến mãi</th>
                            <th>Sản phẩm</th>
                            <th>Loại giảm giá</th>
                            <th>Giá trị</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promotions as $promotion)
                            <tr>
                                <td>{{ $promotion->id }}</td>
                                <td>
                                    <strong>{{ $promotion->name }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($promotion->description, 50) }}</small>
                                </td>
                                <td>
                                    @if($promotion->product)
                                        <span class="badge bg-info">{{ $promotion->product->name }}</span>
                                    @else
                                        <span class="text-muted">Tất cả sản phẩm</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $promotion->discount_type === 'percentage' ? 'warning' : 'success' }}">
                                        {{ $promotion->discount_type === 'percentage' ? 'Phần trăm' : 'Số tiền' }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ number_format($promotion->discount_value, 0, ',', '.') }}{{ $promotion->discount_type === 'percentage' ? '%' : 'đ' }}</strong>
                                </td>
                                <td>
                                    <small>
                                        <strong>Từ:</strong> {{ $promotion->start_date->format('d/m/Y') }}<br>
                                        <strong>Đến:</strong> {{ $promotion->end_date->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>
                                    @if($promotion->is_active && now()->between($promotion->start_date, $promotion->end_date))
                                        <span class="badge bg-success">Đang hoạt động</span>
                                    @elseif($promotion->is_active && now()->lt($promotion->start_date))
                                        <span class="badge bg-info">Sắp diễn ra</span>
                                    @elseif($promotion->is_active && now()->gt($promotion->end_date))
                                        <span class="badge bg-secondary">Đã hết hạn</span>
                                    @else
                                        <span class="badge bg-danger">Tạm dừng</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.promotions.show', $promotion) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Bạn có chắc muốn xóa khuyến mãi này?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $promotions->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-gift fa-2x text-muted mb-2"></i>
                <p class="text-muted">Chưa có khuyến mãi nào</p>
                <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Thêm khuyến mãi đầu tiên
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
