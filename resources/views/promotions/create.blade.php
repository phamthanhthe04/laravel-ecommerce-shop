@extends('layouts.admin')

@section('title', 'Tạo khuyến mãi mới')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tạo khuyến mãi mới</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.promotions.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Tên khuyến mãi -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Tên khuyến mãi <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
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
                                      placeholder="Mô tả chi tiết về khuyến mãi...">{{ old('description') }}</textarea>
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
                                            {{ old('product_id') == $product->id ? 'selected' : '' }}>
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
                                    <input class="form-check-input" type="radio" name="discount_type" id="discount_percentage" value="percentage" {{ old('discount_type', 'percentage') == 'percentage' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="discount_percentage">
                                        <i class="fas fa-percentage me-1"></i>Theo phần trăm (%)
                                    </label>
                                </div>
                                <div class="form-check-inline ms-4">
                                    <input class="form-check-input" type="radio" name="discount_type" id="discount_fixed" value="fixed" {{ old('discount_type') == 'fixed' ? 'checked' : '' }}>
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
                                               value="{{ old('discount_value') }}" 
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
                                           value="{{ old('start_date', now()->format('Y-m-d\TH:i')) }}" 
                                           required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                           value="{{ old('end_date') }}" 
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
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Kích hoạt khuyến mãi ngay
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Nếu không chọn, khuyến mãi sẽ ở trạng thái tạm dừng và cần kích hoạt thủ công.
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Tạo khuyến mãi
                        </button>
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
    // Function to handle discount type change
    function handleDiscountTypeChange() {
        const discountType = $('input[name="discount_type"]:checked').val();
        const discountUnit = $('#discount-unit');
        const discountHelp = $('#discount-help');
        const discountInput = $('#discount_value');
        
        if (discountType === 'percentage') {
            discountUnit.text('%');
            discountHelp.text('Nhập phần trăm giảm giá (0-100)');
            discountInput.attr('max', '100');
        } else {
            discountUnit.text('VNĐ');
            discountHelp.text('Nhập số tiền giảm giá (VNĐ)');
            discountInput.removeAttr('max');
        }
        
        calculateDiscountedPrice();
    }
    
    // Function to calculate discounted price
    function calculateDiscountedPrice() {
        const productSelect = $('#product_id');
        const discountInput = $('#discount_value');
        const previewInput = $('#discounted_price_preview');
        const discountType = $('input[name="discount_type"]:checked').val();
        
        const selectedOption = productSelect.find('option:selected');
        const originalPrice = parseFloat(selectedOption.data('price'));
        const discountValue = parseFloat(discountInput.val());
        
        if (originalPrice && discountValue >= 0) {
            let discountedPrice;
            
            if (discountType === 'percentage') {
                const discountAmount = originalPrice * (discountValue / 100);
                discountedPrice = originalPrice - discountAmount;
            } else {
                discountedPrice = Math.max(0, originalPrice - discountValue);
            }
            
            previewInput.val(Math.round(discountedPrice).toLocaleString('vi-VN'));
        } else {
            previewInput.val('');
        }
    }
    
    // Event listeners
    $('input[name="discount_type"]').on('change', handleDiscountTypeChange);
    $('#product_id, #discount_value').on('change input', calculateDiscountedPrice);
    
    // Initialize
    handleDiscountTypeChange();
    
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
    
    // Initialize calculation if values are already set
    calculateDiscountedPrice();
});
</script>
@endpush
