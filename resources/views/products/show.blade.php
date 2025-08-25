@extends('layouts.admin')

@section('title', 'Chi tiết sản phẩm')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Chi tiết sản phẩm</h2>
        <div>
            <a href="{{ route('admin.manage.products.edit', $product) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-2"></i>Sửa
            </a>
            <a href="{{ route('admin.products') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>
    </div>
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </div>

            <!-- Product Details -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->name }}" 
                                     class="img-fluid rounded shadow">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                     style="height: 300px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-8">
                            <h3>{{ $product->name }}</h3>
                            
                            <div class="mb-3">
                                <span class="badge bg-secondary fs-6">{{ $product->category->name ?? 'Không có danh mục' }}</span>
                                <span class="badge bg-{{ $product->stock_quantity > 0 ? 'success' : 'danger' }} fs-6">
                                    {{ $product->stock_quantity > 0 ? 'Còn hàng' : 'Hết hàng' }}
                                </span>
                                
                                <!-- Hiển thị khuyến mãi nếu có -->
                                @if($product->promotions && $product->promotions->count() > 0)
                                    @foreach($product->promotions as $promotion)
                                        <span class="badge bg-{{ $promotion->is_active && $promotion->start_date <= now() && $promotion->end_date >= now() ? 'warning' : 'secondary' }} fs-6">
                                            <i class="fas fa-gift me-1"></i>{{ $promotion->name }} (-{{ $promotion->discount_percentage }}%)
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                            
                            <!-- Hiển thị giá với khuyến mãi -->
                            @php
                                $activePromotion = $product->promotions ? $product->promotions->where('is_active', true)->where('start_date', '<=', now())->where('end_date', '>=', now())->first() : null;
                                $discountedPrice = $activePromotion ? $activePromotion->calculateDiscountedPrice($product->price) : null;
                            @endphp
                            
                            @if($discountedPrice && $discountedPrice < $product->price)
                                <div class="mb-3">
                                    <h5 class="text-muted text-decoration-line-through mb-1">
                                        Giá gốc: {{ number_format($product->price, 0, ',', '.') }}đ
                                    </h5>
                                    <h4 class="text-danger fw-bold mb-2">
                                        Giá khuyến mãi: {{ number_format($discountedPrice, 0, ',', '.') }}đ
                                        <small class="badge bg-danger ms-2">Tiết kiệm {{ number_format($product->price - $discountedPrice, 0, ',', '.') }}đ</small>
                                    </h4>
                                </div>
                            @else
                                <h4 class="text-primary mb-3">{{ number_format($product->price, 0, ',', '.') }}đ</h4>
                            @endif
                            
                            <div class="mb-3">
                                <strong>Mô tả:</strong>
                                <p class="mt-2">{{ $product->description ?? 'Chưa có mô tả' }}</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Số lượng tồn kho:</strong>
                                    <span class="badge bg-info fs-6">{{ $product->stock_quantity ?? 0 }}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Ngày tạo:</strong>
                                    <span class="text-muted">{{ $product->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                            
                            <!-- Hiển thị thông tin khuyến mãi chi tiết -->
                            @if($product->promotions && $product->promotions->count() > 0)
                            <div class="mt-4">
                                <h6><i class="fas fa-gift me-2"></i>Thông tin khuyến mãi:</h6>
                                @foreach($product->promotions as $promotion)
                                    <div class="alert alert-{{ $promotion->is_active && $promotion->start_date <= now() && $promotion->end_date >= now() ? 'warning' : 'secondary' }} mb-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>{{ $promotion->name }}</strong>
                                                <br><small>{{ $promotion->description }}</small>
                                                <br><small>Giảm: {{ $promotion->discount_percentage }}%</small>
                                            </div>
                                            <div class="text-end">
                                                <small>{{ $promotion->start_date->format('d/m/Y') }} - {{ $promotion->end_date->format('d/m/Y') }}</small>
                                                <br><span class="badge bg-{{ $promotion->is_active && $promotion->start_date <= now() && $promotion->end_date >= now() ? 'success' : 'secondary' }}">
                                                    {{ $promotion->is_active && $promotion->start_date <= now() && $promotion->end_date >= now() ? 'Đang áp dụng' : 'Không áp dụng' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ route('admin.promotions.show', $promotion) }}" class="btn btn-sm btn-outline-primary">
                                                Xem chi tiết khuyến mãi
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                            
                            @if($product->updated_at != $product->created_at)
                            <div class="mt-2">
                                <strong>Cập nhật lần cuối:</strong>
                                <span class="text-muted">{{ $product->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.manage.products.edit', $product) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                        </a>
                        <form method="POST" action="{{ route('admin.manage.products.destroy', $product) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                <i class="fas fa-trash me-2"></i>Xóa
                            </button>
                        </form>
                        <a href="{{ route('shop.show', $product) }}" class="btn btn-info" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>Xem trên shop
                        </a>
                        <a href="{{ route('admin.promotions.create') }}?product_id={{ $product->id }}" class="btn btn-warning">
                            <i class="fas fa-gift me-2"></i>Tạo khuyến mãi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection