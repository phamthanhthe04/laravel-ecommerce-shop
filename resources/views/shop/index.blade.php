@extends('layouts.app')

@section('title', 'Cửa hàng')

@section('content')
<!-- Hero Section -->
<div class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Chào mừng đến Shop Online</h1>
        <p class="lead">Khám phá những sản phẩm tuyệt vời với giá cả hợp lý</p>
        <a href="#products" class="btn btn-light btn-lg">
            <i class="fas fa-shopping-bag me-2"></i>Mua sắm ngay
        </a>
    </div>
</div>

<div class="container py-5" id="products">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-filter me-2"></i>Bộ lọc</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('shop.index') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                placeholder="Tên sản phẩm...">
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label">Danh mục</label>
                            <select name="category_id" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sort -->
                        <div class="mb-3">
                            <label class="form-label">Sắp xếp</label>
                            <select name="sort" class="form-select">
                                <option value="">Mới nhất</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                    Giá: Thấp đến cao
                                </option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                    Giá: Cao đến thấp
                                </option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>
                                    Tên A-Z
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Áp dụng
                        </button>
                        <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-undo me-2"></i>Reset
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Sản phẩm ({{ $products->total() }} kết quả)</h4>
                <div>
                    <small class="text-muted">
                        Hiển thị {{ $products->firstItem() }}-{{ $products->lastItem() }}
                        trong tổng số {{ $products->total() }} sản phẩm
                    </small>
                </div>
            </div>

            @if($products->count() > 0)
            <div class="row">
                @foreach($products as $product)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card product-card h-100">
                        <img src="{{ $product->image ?? 'https://via.placeholder.com/300x200' }}" class="card-img-top"
                            alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text text-muted small mb-2">
                                <i class="fas fa-tags me-1"></i>{{ $product->category->name }}
                                
                                <!-- Hiển thị khuyến mãi nếu có -->
                                @php
                                    $activePromotion = $product->getBestActivePromotion();
                                @endphp
                                @if($activePromotion)
                                    @if($activePromotion->discount_type === 'percentage')
                                        <span class="badge bg-warning text-dark ms-2">
                                            <i class="fas fa-percentage me-1"></i>-{{ $activePromotion->discount_value }}%
                                        </span>
                                    @else
                                        <span class="badge bg-info text-white ms-2">
                                            <i class="fas fa-minus-circle me-1"></i>-{{ number_format($activePromotion->discount_value, 0, ',', '.') }}đ
                                        </span>
                                    @endif
                                @endif
                            </p>
                            <p class="card-text flex-grow-1">
                                {{ Str::limit($product->description, 100) }}
                            </p>

                            <div class="mt-auto">
                                <!-- Hiển thị giá với khuyến mãi -->
                                @if($activePromotion)
                                    @php
                                        $discountedPrice = $product->getDiscountedPrice();
                                    @endphp
                                @else
                                    @php
                                        $discountedPrice = null;
                                    @endphp
                                @endif
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        @if($discountedPrice && $discountedPrice < $product->price)
                                            <h6 class="text-muted text-decoration-line-through mb-1">
                                                {{ number_format($product->price, 0, ',', '.') }}đ
                                            </h6>
                                            <h5 class="text-danger mb-0 fw-bold">
                                                {{ number_format($discountedPrice, 0, ',', '.') }}đ
                                            </h5>
                                        @else
                                            <h5 class="text-primary mb-0">
                                                {{ number_format($product->price, 0, ',', '.') }}đ
                                            </h5>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        Còn: {{ $product->stock_quantity }}
                                    </small>
                                </div>

                                <div class="d-grid gap-2">
                                    <a href="{{ route('shop.show', $product) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                                    </a>
                                    @auth
                                    <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-cart-plus me-1"></i>Thêm vào giỏ
                                        </button>
                                    </form>
                                    @else
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập để mua
                                    </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $products->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>Không tìm thấy sản phẩm nào</h4>
                <p class="text-muted">Hãy thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary">
                    <i class="fas fa-undo me-2"></i>Xem tất cả sản phẩm
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Update cart count after adding to cart
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});
</script>
@endsection