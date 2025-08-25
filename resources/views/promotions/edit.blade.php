@extends('layouts.admin')

@section('title', 'Chỉnh sửa khuyến mãi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chỉnh sửa khuyến mãi: {{ $promotion->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.promotions.update', $promotion) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Thông tin hiện tại -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Thông tin hiện tại:</h6>
                            <ul class="mb-0">
                                <li><strong>Trạng thái:</strong> 
                                    @if($promotion->status === 'active')
                                        <span class="badge bg-success">Đang hoạt động</span>
                                    @elseif($promotion->status === 'scheduled')
                                        <span class="badge bg-info">Đã lên lịch</span>
                                    @elseif($promotion->status === 'expired')
                                        <span class="badge bg-secondary">Đã hết hạn</span>
                                    @else
                                        <span class="badge bg-danger">Không hoạt động</span>
                                    @endif
                                </li>
                                <li><strong>Sản phẩm hiện tại:</strong> {{ $promotion->product->name }}</li>
                                <li><strong>Giảm giá hiện tại:</strong> {{ $promotion->discount_percentage }}%</li>
                            </ul>
                        </div>

                        <!-- Tên khuyến mãi -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $promotion->name) }}" 
                                   placeholder="Nhập tên khuyến mãi..."
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mô tả -->
                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Mô tả chi tiết về khuyến mãi...">{{ old('description', $promotion->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sản phẩm -->
                        <div class="form-group mb-3">
                            <label for="product_id" class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                            <select class="form-control @error('product_id') is-invalid @enderror" 
                                    id="product_id" 
                                    name="product_id" 
                                    required>
                                <option value="">-- Chọn sản phẩm --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-price="{{ $product->price }}"
                                            {{ old('product_id', $promotion->product_id) == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} - {{ number_format($product->price) }}đ
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Loại giảm giá -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                                <div class="form-check-inline">
                                    <input class="form-check-input" type="radio" name="discount_type" id="discount_percentage" value="percentage" {{ old('discount_type', $promotion->discount_type ?? 'percentage') == 'percentage' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="discount_percentage">
                                        <i class="fas fa-percentage me-1"></i>Theo phần trăm (%)
                                    </label>
                                </div>
                                <div class="form-check-inline ms-4">
                                    <input class="form-check-input" type="radio" name="discount_type" id="discount_fixed" value="fixed" {{ old('discount_type', $promotion->discount_type) == 'fixed' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="discount_fixed">
                                        <i class="fas fa-minus-circle me-1"></i>Số tiền cố định (VNĐ)
                                    </label>
                                </div>
                            </div>

                            <!-- Giá trị giảm giá -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="discount_value" class="form-label">Giá trị giảm giá <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('discount_value') is-invalid @enderror" 
                                               id="discount_value" 
                                               name="discount_value" 
                                               value="{{ old('discount_value', $promotion->discount_value ?? $promotion->discount_percentage ?? '') }}" 
                                               min="0.01" 
                                               step="0.01" 
                                               placeholder="0.00"
                                               required>
                                        <span class="input-group-text" id="discount-unit">%</span>
                                        @error('discount_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted" id="discount-help">
                                        Nhập phần trăm giảm giá (0-100)
                                    </small>
                                </div>
                            </div>

                            <!-- Giá sau giảm (preview) -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Giá sau giảm (preview)</label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control" 
                                               id="discounted_price_preview" 
                                               readonly 
                                               placeholder="Chọn sản phẩm và nhập % giảm giá">
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Ngày bắt đầu -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="start_date" class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" 
                                           name="start_date" 
                                           value="{{ old('start_date', $promotion->start_date->format('Y-m-d\TH:i')) }}" 
                                           required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($promotion->status === 'active')
                                        <small class="form-text text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            Khuyến mãi đang hoạt động. Thay đổi ngày bắt đầu có thể ảnh hưởng đến khách hàng.
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <!-- Ngày kết thúc -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="end_date" class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" 
                                           name="end_date" 
                                           value="{{ old('end_date', $promotion->end_date->format('Y-m-d\TH:i')) }}" 
                                           required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Trạng thái hoạt động -->
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Kích hoạt khuyến mãi
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Bỏ chọn để tạm dừng khuyến mãi mà không cần xóa.
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật khuyến mãi
                        </button>
                        <a href="{{ route('admin.promotions.show', $promotion) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </a>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Function to calculate discounted price
    function calculateDiscountedPrice() {
        const productSelect = $('#product_id');
        const discountInput = $('#discount_percentage');
        const previewInput = $('#discounted_price_preview');
        
        const selectedOption = productSelect.find('option:selected');
        const originalPrice = parseFloat(selectedOption.data('price'));
        const discountPercentage = parseFloat(discountInput.val());
        
        if (originalPrice && discountPercentage >= 0) {
            const discountAmount = originalPrice * (discountPercentage / 100);
            const discountedPrice = originalPrice - discountAmount;
            previewInput.val(Math.round(discountedPrice).toLocaleString('vi-VN'));
        } else {
            previewInput.val('');
        }
    }
    
    // Event listeners for real-time calculation
    $('#product_id, #discount_percentage').on('change input', calculateDiscountedPrice);
    
    // Set minimum end date based on start date
    $('#start_date').on('change', function() {
        const startDate = $(this).val();
        if (startDate) {
            $('#end_date').attr('min', startDate);
        }
    });
    
    // Initialize minimum end date
    const initialStartDate = $('#start_date').val();
    if (initialStartDate) {
        $('#end_date').attr('min', initialStartDate);
    }
    
    // Validate that end date is after start date
    $('#end_date').on('change', function() {
        const startDate = $('#start_date').val();
        const endDate = $(this).val();
        
        if (startDate && endDate && endDate <= startDate) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback">Ngày kết thúc phải sau ngày bắt đầu.</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });
    
    // Initialize calculation with current values
    calculateDiscountedPrice();
});
</script>
@endpush
