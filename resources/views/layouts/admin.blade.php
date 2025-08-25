<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .admin-sidebar {
            background-color: #343a40;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .admin-sidebar .sidebar-header {
            padding: 20px;
            background-color: #2c3034;
            border-bottom: 1px solid #495057;
        }
        
        .admin-sidebar .sidebar-header h3 {
            color: #fff;
            margin: 0;
            font-size: 1.2rem;
        }
        
        .admin-nav {
            padding: 20px 0;
        }
        
        .admin-nav .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            border: none;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .admin-nav .nav-link:hover {
            background-color: #495057;
            color: #fff;
        }
        
        .admin-nav .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        
        .admin-nav .nav-link i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }
        
        .admin-content {
            margin-left: 250px;
            min-height: 100vh;
            background-color: #fff;
        }
        
        .admin-topbar {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 30px;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .admin-main {
            padding: 30px;
        }
        
        .card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                margin-left: -250px;
            }
            .admin-sidebar.show {
                margin-left: 0;
            }
            .admin-content {
                margin-left: 0;
            }
        }
        
        .sidebar-toggle {
            display: none;
        }
        
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
        }
        
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        @media (max-width: 768px) {
            .overlay.show {
                display: block;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Sidebar -->
    <nav class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</h3>
        </div>
        <ul class="nav flex-column admin-nav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                   href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" 
                   href="{{ route('admin.users') }}">
                    <i class="fas fa-users"></i> Người dùng
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.categories') ? 'active' : '' }}" 
                   href="{{ route('admin.categories') }}">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.products') ? 'active' : '' }}" 
                   href="{{ route('admin.products') }}">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}" 
                   href="{{ route('admin.promotions.index') }}">
                    <i class="fas fa-gift"></i> Khuyến mãi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}" 
                   href="{{ route('admin.orders') }}">
                    <i class="fas fa-shopping-cart"></i> Đơn hàng
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Top Bar -->
        <div class="admin-topbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary sidebar-toggle me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mb-0">@yield('title', 'Admin Panel')</h5>
            </div>
            
            <div class="dropdown">
                <a class="btn btn-outline-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user me-1"></i>{{ Auth::user()->name ?? 'Admin' }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('shop.index') }}">
                        <i class="fas fa-store me-2"></i>Xem Shop
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Page Content -->
        <div class="admin-main">
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Có lỗi xảy ra:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        // Toggle sidebar on mobile
        sidebarToggle.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
        });
        
        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    });
    </script>
    @stack('scripts')
</body>
</html>
