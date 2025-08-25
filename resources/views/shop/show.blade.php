@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('shop.index') }}">Trang chủ</a></li>
            <li class="breadcrumb-item"><a
                    href="{{ route('shop.index') }}?category_id={{ $product->category_id }}">{{ $product->category->name }}</a>
            </li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <img src="{{ $product->image ?? 'https://via.placeholder.com/500x400' }}" class="img-fluid rounded shadow"
                alt="{{ $product->name }}">
        </div>

        <div class="col-md-6">
            <h1 class="mb-3">{{ $product->name }}</h1>

            <div class="mb-3">
                <span class="badge bg-primary">{{ $product->category->name }}</span>
                @if($product->stock_quantity > 0)
                <span class="badge bg-success">Còn hàng</span>
                @else
                <span class="badge bg-danger">Hết hàng</span>
                @endif
                
                <!-- Hiển thị khuyến mãi nếu có -->
                @php
                    $activePromotion = $product->getBestActivePromotion();
                @endphp
                @if($activePromotion)
                    @if($activePromotion->discount_type === 'percentage')
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-percentage me-1"></i>-{{ $activePromotion->discount_value }}%
                        </span>
                    @else
                        <span class="badge bg-info text-white">
                            <i class="fas fa-minus-circle me-1"></i>-{{ number_format($activePromotion->discount_value, 0, ',', '.') }}đ
                        </span>
                    @endif
                @endif
            </div>

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
            
            @if($discountedPrice && $discountedPrice < $product->price)
                <div class="mb-4">
                    <h3 class="text-muted text-decoration-line-through mb-1">
                        {{ number_format($product->price, 0, ',', '.') }}đ
                    </h3>
                    <h2 class="text-danger fw-bold mb-2">
                        {{ number_format($discountedPrice, 0, ',', '.') }}đ
                        <small class="badge bg-danger ms-2">Tiết kiệm {{ number_format($product->price - $discountedPrice, 0, ',', '.') }}đ</small>
                    </h2>
                    @if($activePromotion)
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-gift me-2"></i>
                            <strong>{{ $activePromotion->name }}</strong> - {{ $activePromotion->description }}
                            <br><small>Khuyến mãi có hiệu lực đến: {{ $activePromotion->end_date->format('d/m/Y') }}</small>
                        </div>
                    @endif
                </div>
            @else
                <h3 class="text-primary mb-4">{{ number_format($product->price, 0, ',', '.') }}đ</h3>
            @endif

            <div class="mb-4">
                <h5>Mô tả sản phẩm:</h5>
                <p>{{ $product->description }}</p>
            </div>

            <div class="mb-4">
                <strong>Số lượng còn lại: </strong>
                <span class="text-muted">{{ $product->stock_quantity }}</span>
            </div>

            @if($product->stock_quantity > 0)
            @auth
            <div class="mb-3">
                <label for="quantity" class="form-label">Số lượng:</label>
                <input type="number" id="quantity" class="form-control" value="1" min="1"
                    max="{{ $product->stock_quantity }}" style="width: 100px; display: inline-block;">
            </div>

            <div class="d-grid gap-2 d-md-flex">
                <button onclick="addToCart('{{ $product->id }}')" class="btn btn-primary btn-lg">
                    <i class="fas fa-cart-plus me-2"></i>Thêm vào giỏ hàng
                </button>
                <button onclick="buyNow('{{ $product->id }}')" class="btn btn-success btn-lg">
                    <i class="fas fa-bolt me-2"></i>Mua ngay
                </button>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Vui lòng <a href="{{ route('login') }}">đăng nhập</a> để mua sản phẩm
            </div>
            @endauth
            @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Sản phẩm hiện đang hết hàng
            </div>
            @endif
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <hr class="my-5">
    <h3 class="mb-4">Sản phẩm liên quan</h3>
    <div class="row">
        @foreach($relatedProducts as $relatedProduct)
        <div class="col-md-3 mb-4">
            <div class="card product-card h-100">
                <img src="{{ $relatedProduct->image ?? 'https://via.placeholder.com/300x200' }}" class="card-img-top"
                    alt="{{ $relatedProduct->name }}" style="height: 150px; object-fit: cover;">

                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">{{ $relatedProduct->name }}</h6>
                    <p class="card-text text-primary fw-bold">
                        {{ number_format($relatedProduct->price, 0, ',', '.') }}đ
                    </p>

                    <div class="mt-auto">
                        <a href="{{ route('shop.show', $relatedProduct) }}"
                            class="btn btn-outline-primary btn-sm w-100">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function addToCart(productId) {
    const quantity = document.getElementById('quantity').value;

    fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: parseInt(quantity)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đã thêm sản phẩm vào giỏ hàng!');
                updateCartCount();
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra!');
        });
}

function buyNow(productId) {
    addToCart(productId);
    setTimeout(() => {
        window.location.href = '{{ route("cart.index") }}';
    }, 1000);
}
</script>
@endsection