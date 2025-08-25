@extends('layouts.admin')

@section('title', 'Chi tiết khuyến mãi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết khuyến mãi</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Thông tin chính -->
                        <div class="col-md-8">
                            <h4>{{ $promotion->name }}</h4>
                            
                            @if($promotion->description)
                                <div class="mb-3">
                                    <strong>Mô tả:</strong>
                                    <p class="text-muted">{{ $promotion->description }}</p>
                                </div>
                            @endif

                            <!-- Thông tin sản phẩm -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Thông tin sản phẩm</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6>
                                                <a href="{{ route('admin.manage.products.show', $promotion->product) }}" 
                                                   class="text-decoration-none">
                                                    {{ $promotion->product->name }}
                                                </a>
                                            </h6>
                                            <p class="text-muted mb-1">
                                                <strong>Danh mục:</strong> {{ $promotion->product->category->name ?? 'Chưa phân loại' }}
                                            </p>
                                            <p class="text-muted mb-0">
                                                <strong>Mô tả:</strong> {{ Str::limit($promotion->product->description, 100) ?: 'Không có mô tả' }}
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="pricing-info">
                                                <div class="original-price">
                                                    <span class="text-muted text-decoration-line-through">
                                                        {{ number_format($promotion->product->price) }}đ
                                                    </span>
                                                </div>
                                                <div class="discounted-price">
                                                    <h5 class="text-danger mb-0">
                                                        {{ number_format($promotion->product->getDiscountedPrice()) }}đ
                                                    </h5>
                                                </div>
                                                <div class="discount-badge">
                                                    <span class="badge bg-warning text-dark">
                                                        -{{ $promotion->discount_percentage }}%
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Thông tin thời gian -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Ngày bắt đầu</h6>
                                            <p class="card-text">
                                                <strong>{{ $promotion->start_date->format('d/m/Y') }}</strong><br>
                                                <small class="text-muted">{{ $promotion->start_date->format('H:i') }}</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Ngày kết thúc</h6>
                                            <p class="card-text">
                                                <strong>{{ $promotion->end_date->format('d/m/Y') }}</strong><br>
                                                <small class="text-muted">{{ $promotion->end_date->format('H:i') }}</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar thông tin -->
                        <div class="col-md-4">
                            <!-- Trạng thái -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Trạng thái</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        @if($promotion->status === 'active')
                                            <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                                            <h6 class="text-success">Đang hoạt động</h6>
                                        @elseif($promotion->status === 'scheduled')
                                            <i class="fas fa-clock fa-3x text-info mb-2"></i>
                                            <h6 class="text-info">Đã lên lịch</h6>
                                        @elseif($promotion->status === 'expired')
                                            <i class="fas fa-times-circle fa-3x text-secondary mb-2"></i>
                                            <h6 class="text-secondary">Đã hết hạn</h6>
                                        @else
                                            <i class="fas fa-pause-circle fa-3x text-danger mb-2"></i>
                                            <h6 class="text-danger">Không hoạt động</h6>
                                        @endif
                                    </div>
                                    
                                    <hr>
                                    
                                    <!-- Toggle Switch -->
                                    <div class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input promotion-toggle" 
                                                   type="checkbox" 
                                                   data-id="{{ $promotion->id }}"
                                                   {{ $promotion->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                {{ $promotion->is_active ? 'Đang bật' : 'Đang tắt' }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Thống kê -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Thống kê</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="mb-2">
                                            <strong>Tiết kiệm tối đa mỗi sản phẩm:</strong><br>
                                            <span class="text-success">
                                                {{ number_format($promotion->product->price - $promotion->product->getDiscountedPrice()) }}đ
                                            </span>
                                        </div>
                                        
                                        @if($promotion->status === 'active')
                                            <div class="mb-2">
                                                <strong>Thời gian còn lại:</strong><br>
                                                <span class="text-info" id="countdown">
                                                    {{ $promotion->end_date->diffForHumans() }}
                                                </span>
                                            </div>
                                        @elseif($promotion->status === 'scheduled')
                                            <div class="mb-2">
                                                <strong>Bắt đầu sau:</strong><br>
                                                <span class="text-info">
                                                    {{ $promotion->start_date->diffForHumans() }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Thông tin khác -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Thông tin khác</h6>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted">
                                        <strong>ID:</strong> {{ $promotion->id }}<br>
                                        <strong>Tạo lúc:</strong> {{ $promotion->created_at->format('d/m/Y H:i') }}<br>
                                        <strong>Cập nhật:</strong> {{ $promotion->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <button type="button" 
                                class="btn btn-danger" 
                                onclick="confirmDelete()">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                    <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary float-end">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete confirmation modal -->
<form id="delete-form" action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle promotion toggle
    $('.promotion-toggle').on('change', function() {
        const promotionId = $(this).data('id');
        const isActive = $(this).is(':checked');
        const toggle = $(this);
        const label = $(this).siblings('label');
        
        $.ajax({
            url: `/admin/promotions/${promotionId}/toggle-status`,
            method: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                toggle.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    label.text(response.is_active ? 'Đang bật' : 'Đang tắt');
                    
                    // Show toast notification
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                    
                    // Reload page after 1 second to update status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                // Revert toggle state
                toggle.prop('checked', !isActive);
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Không thể cập nhật trạng thái khuyến mãi. Vui lòng thử lại.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            },
            complete: function() {
                toggle.prop('disabled', false);
            }
        });
    });
});

function confirmDelete() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Bạn có chắc chắn?',
            text: "Khuyến mãi này sẽ bị xóa vĩnh viễn!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Có, xóa nó!',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form').submit();
            }
        });
    } else {
        if (confirm('Bạn có chắc chắn muốn xóa khuyến mãi này?')) {
            document.getElementById('delete-form').submit();
        }
    }
}
</script>
@endpush
