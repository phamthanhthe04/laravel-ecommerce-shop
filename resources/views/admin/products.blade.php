@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý sản phẩm</h2>
    <a href="{{ route('admin.manage.products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Thêm sản phẩm
    </a>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-box me-2"></i>Danh sách sản phẩm</h5>
    </div>
    <div class="card-body">
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá gốc</th>
                            <th>Khuyến mãi</th>
                            <th>Giá sau KM</th>
                            <th>Số lượng</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                             alt="{{ $product->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name ?? 'Chưa phân loại' }}</td>
                                <td>{{ number_format($product->price, 0, ',', '.') }}đ</td>
                                <td>
                                    @php
                                        $activePromotion = $product->getBestActivePromotion();
                                    @endphp
                                    @if($activePromotion)
                                        @if($activePromotion->discount_type === 'percentage')
                                            <span class="badge bg-success">
                                                <i class="fas fa-percentage me-1"></i>
                                                Giảm {{ $activePromotion->discount_value }}%
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                ({{ number_format($product->price * $activePromotion->discount_value / 100, 0, ',', '.') }}đ)
                                            </small>
                                        @else
                                            <span class="badge bg-info">
                                                <i class="fas fa-minus-circle me-1"></i>
                                                Giảm {{ number_format($activePromotion->discount_value, 0, ',', '.') }}đ
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                ({{ number_format(($activePromotion->discount_value / $product->price) * 100, 1) }}%)
                                            </small>
                                        @endif
                                        <br>
                                        <small class="text-success">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $activePromotion->start_date->format('d/m') }} - {{ $activePromotion->end_date->format('d/m') }}
                                        </small>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-times me-1"></i>
                                            Không có KM
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($activePromotion)
                                        <div class="d-flex flex-column">
                                            <strong class="text-success">{{ number_format($product->getDiscountedPrice(), 0, ',', '.') }}đ</strong>
                                            <small class="text-muted text-decoration-line-through">{{ number_format($product->price, 0, ',', '.') }}đ</small>
                                            <small class="text-danger">
                                                <i class="fas fa-arrow-down me-1"></i>
                                                Tiết kiệm {{ number_format($product->price - $product->getDiscountedPrice(), 0, ',', '.') }}đ
                                            </small>
                                        </div>
                                    @else
                                        <strong>{{ number_format($product->price, 0, ',', '.') }}đ</strong>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $product->stock_quantity > 0 ? 'success' : 'danger' }}">
                                        {{ $product->stock_quantity }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                        {{ $product->is_active ? 'Hoạt động' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.manage.products.show', $product) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.manage.products.edit', $product) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.manage.products.destroy', $product) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
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
                {{ $products->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-box fa-2x text-muted mb-2"></i>
                <p class="text-muted">Chưa có sản phẩm nào</p>
                <a href="{{ route('admin.manage.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Thêm sản phẩm đầu tiên
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
