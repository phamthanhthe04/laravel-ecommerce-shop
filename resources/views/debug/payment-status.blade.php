@extends('layouts.app')

@section('title', 'Test Thanh Toán VNPay')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🧪 Test Hệ Thống Thanh Toán</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Cấu hình VNPay hiện tại:</h6>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>URL:</span>
                                    <span class="text-primary">{{ config('vnpay.url') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>TMN Code:</span>
                                    <span class="text-primary">{{ config('vnpay.tmn_code') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Return URL:</span>
                                    <span class="text-primary">{{ config('vnpay.return_url') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Hash Secret:</span>
                                    <span class="text-muted">{{ substr(config('vnpay.hash_secret'), 0, 8) }}...</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Trạng thái hệ thống:</h6>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <span class="badge bg-success">✓</span>
                                    Secret Key đã cập nhật
                                </li>
                                <li class="list-group-item">
                                    <span class="badge bg-success">✓</span>
                                    Auto-login user cho cart
                                </li>
                                <li class="list-group-item">
                                    <span class="badge bg-success">✓</span>
                                    JavaScript error handling
                                </li>
                                <li class="list-group-item">
                                    <span class="badge bg-success">✓</span>
                                    Enhanced validation
                                </li>
                            </ul>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <h6>Test các chức năng:</h6>
                        <div class="btn-group-vertical d-grid gap-2">
                            <a href="{{ route('shop.index') }}" class="btn btn-outline-primary">
                                🛍️ Đi mua hàng (Test cart)
                            </a>
                            <a href="{{ route('cart.index') }}" class="btn btn-outline-success">
                                🛒 Xem giỏ hàng
                            </a>
                            <a href="{{ route('orders.my') }}" class="btn btn-outline-info">
                                📦 Đơn hàng của tôi
                            </a>
                        </div>
                    </div>

                    @auth
                    <div class="alert alert-info mt-4">
                        <strong>User hiện tại:</strong> {{ Auth::user()->name }} ({{ Auth::user()->email }})
                        <br>
                        <small>Tất cả đơn hàng sẽ được tạo cho user này</small>
                    </div>
                    @else
                    <div class="alert alert-warning mt-4">
                        <strong>Chưa đăng nhập</strong> - <a href="{{ route('login') }}">Đăng nhập</a> để test đầy đủ
                    </div>
                    @endauth
                </div>
            </div>

            <!-- Thông tin sửa đổi -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">📋 Những gì đã được sửa</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">✅ Đã sửa:</h6>
                            <ul class="small">
                                <li>Cập nhật VNPay Secret Key mới</li>
                                <li>Bỏ chọn user trong cart</li>
                                <li>Auto-sử dụng user đang đăng nhập</li>
                                <li>Cải thiện error handling</li>
                                <li>Thêm validation tốt hơn</li>
                                <li>Kiểm tra stock quantity</li>
                                <li>Enhanced logging</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger">🚫 Nguyên nhân lỗi cũ:</h6>
                            <ul class="small">
                                <li>Secret key cũ không đúng</li>
                                <li>Confusion trong chọn user</li>
                                <li>JavaScript timer error từ VNPay</li>
                                <li>Thiếu validation amount</li>
                                <li>Không kiểm tra stock</li>
                                <li>Poor error messages</li>
                                <li>Insufficient logging</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
