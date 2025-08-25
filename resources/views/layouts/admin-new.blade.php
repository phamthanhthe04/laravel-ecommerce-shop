@extends('layouts.app')

@section('title', $title ?? 'Admin Panel')

@push('styles')
<style>
    .admin-layout {
        display: flex;
        min-height: 100vh;
    }
    
    .admin-sidebar {
        width: 250px;
        background-color: #f8f9fa;
        border-right: 1px solid #dee2e6;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    
    .admin-sidebar.collapsed {
        width: 60px;
    }
    
    .admin-sidebar.collapsed .nav-text {
        display: none;
    }
    
    .admin-sidebar.collapsed .sidebar-title {
        display: none;
    }
    
    .admin-main {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .admin-topbar {
        background-color: #fff;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .admin-content {
        flex-grow: 1;
        padding: 2rem;
        background-color: #f8f9fa;
    }
    
    .sidebar-nav {
        padding: 1rem 0;
    }
    
    .sidebar-nav .nav-link {
        color: #495057;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .sidebar-nav .nav-link:hover,
    .sidebar-nav .nav-link.active {
        background-color: #007bff;
        color: white;
    }
    
    .sidebar-nav .nav-link i {
        width: 20px;
        margin-right: 10px;
        text-align: center;
    }
    
    .toggle-btn {
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        .admin-sidebar {
            position: fixed;
            left: -250px;
            top: 0;
            height: 100vh;
            z-index: 1000;
        }
        
        .admin-sidebar.show {
            left: 0;
        }
        
        .admin-main {
            width: 100%;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-layout">
    <!-- Sidebar -->
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="p-3">
            <h5 class="sidebar-title">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span class="nav-text">Admin Panel</span>
            </h5>
        </div>
        
        <div class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" 
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="{{ route('admin.users') }}" 
               class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span class="nav-text">Người dùng</span>
            </a>
            <a href="{{ route('admin.categories') }}" 
               class="nav-link {{ request()->routeIs('admin.categories') ? 'active' : '' }}">
                <i class="fas fa-tags"></i>
                <span class="nav-text">Danh mục</span>
            </a>
            <a href="{{ route('admin.products') }}" 
               class="nav-link {{ request()->routeIs('admin.products') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span class="nav-text">Sản phẩm</span>
            </a>
            <a href="{{ route('admin.promotions.index') }}" 
               class="nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
                <i class="fas fa-gift"></i>
                <span class="nav-text">Khuyến mãi</span>
            </a>
            <a href="{{ route('admin.orders') }}" 
               class="nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span class="nav-text">Đơn hàng</span>
            </a>
        </div>
    </nav>
    
    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Main Content -->
    <div class="admin-main">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center">
                <button class="toggle-btn me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h6 class="mb-0 text-muted">{{ config('app.name') }} - Admin Panel</h6>
            </div>
            
            <div class="d-flex align-items-center">
                <!-- Quick Menu -->
                <div class="dropdown me-3">
                    <button class="btn btn-outline-secondary dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-plus me-1"></i>Thêm mới
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.products') }}?action=create">
                            <i class="fas fa-box me-2"></i>Sản phẩm
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.categories') }}?action=create">
                            <i class="fas fa-tags me-2"></i>Danh mục
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.promotions.create') }}">
                            <i class="fas fa-gift me-2"></i>Khuyến mãi
                        </a></li>
                    </ul>
                </div>
                
                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>{{ Auth::user()->name ?? 'Admin' }}
                    </button>
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
        </header>
        
        <!-- Page Content -->
        <main class="admin-content">
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
            
            <!-- Page Content -->
            @yield('content')
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Toggle sidebar
    toggleBtn.addEventListener('click', function() {
        if (window.innerWidth > 768) {
            // Desktop: collapse sidebar
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        } else {
            // Mobile: show/hide sidebar
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
    });
    
    // Close mobile sidebar when clicking overlay
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });
    
    // Restore desktop sidebar state
    if (window.innerWidth > 768) {
        const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (collapsed) {
            sidebar.classList.add('collapsed');
        }
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        } else {
            sidebar.classList.remove('collapsed');
        }
    });
});
</script>
@stack('scripts')
@endpush
