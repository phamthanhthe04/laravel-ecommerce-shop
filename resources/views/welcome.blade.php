@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
<!-- Hero Section -->
<div class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Chào mừng đến Shop Online</h1>
        <p class="lead">Khám phá những sản phẩm tuyệt vời với giá cả hợp lý</p>
        <div class="mt-4">
            <a href="{{ route('shop.index') }}" class="btn btn-light btn-lg me-3">
                <i class="fas fa-shopping-bag me-2"></i>Mua sắm ngay
            </a>
            @guest
                <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Đăng ký miễn phí
                </a>
            @endguest
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4 text-center mb-4">
            <i class="fas fa-truck fa-3x text-primary mb-3"></i>
            <h4>Giao hàng nhanh chóng</h4>
            <p class="text-muted">Đảm bảo giao hàng trong 24-48h</p>
        </div>
        <div class="col-md-4 text-center mb-4">
            <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
            <h4>Bảo hành chất lượng</h4>
            <p class="text-muted">Cam kết 100% chất lượng sản phẩm</p>
        </div>
        <div class="col-md-4 text-center mb-4">
            <i class="fas fa-headset fa-3x text-primary mb-3"></i>
            <h4>Hỗ trợ 24/7</h4>
            <p class="text-muted">Luôn sẵn sàng hỗ trợ khách hàng</p>
        </div>
    </div>
</div>
@endsection