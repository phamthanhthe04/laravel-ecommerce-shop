<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shop Online')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    .navbar-brand {
        font-weight: bold;
        color: #2c3e50 !important;
    }

    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 100px 0;
    }

    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(45deg, #667eea, #764ba2);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(45deg, #5a6fd8, #6a4190);
    }

    .footer {
        background: #2c3e50;
        color: white;
        padding: 40px 0;
    }

    .cart-count {
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 0.8em;
        position: absolute;
        top: -8px;
        right: -8px;
    }

    .sidebar {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px;
    }

    .admin-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }

    /* Cart page styling */
    .product-checkbox {
        transform: scale(1.2);
    }

    .table-success {
        background-color: #d1edff !important;
    }

    .table-success strong {
        color: #0d6efd;
    }

    /* Chart styling */
    .btn-check:checked + .btn-outline-primary {
        background-color: #667eea;
        border-color: #667eea;
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }
    </style>
    @stack('styles')
    @yield('styles')
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('shop.index') }}">
                <i class="fas fa-store me-2"></i>Shop Online
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('shop.index') }}">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('shop.index') }}">
                            <i class="fas fa-shopping-bag me-1"></i>Sản phẩm
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    @auth
                    @if(auth()->user()->role === 'admin')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-cog me-1"></i>Quản trị
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                            <i class="fas fa-shopping-cart me-1"></i>Giỏ hàng
                            <span class="cart-count" id="cart-count">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('orders.my') }}">
                            <i class="fas fa-list me-1"></i>Đơn hàng
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>{{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="fas fa-user-plus me-1"></i>Đăng ký
                        </a>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show m-0" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show m-0" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Shop Online</h5>
                    <p>Chuyên cung cấp các sản phẩm chất lượng cao với giá cả hợp lý.</p>
                </div>
                <div class="col-md-3">
                    <h5>Liên kết</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('shop.index') }}" class="text-light">Sản phẩm</a></li>
                        <li><a href="#" class="text-light">Về chúng tôi</a></li>
                        <li><a href="#" class="text-light">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Theo dõi</h5>
                    <div>
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4">
            <div class="text-center">
                <p>&copy; {{ date('Y') }} Shop Online. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Update cart count
    function updateCartCount() {
        @auth
        fetch('{{ route("cart.count") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('cart-count').textContent = data.count;
            });
        @endauth
    }

    // Load cart count on page load
    document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
    @yield('scripts')
</body>

</html>