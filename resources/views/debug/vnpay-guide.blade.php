@extends('layouts.app')

@section('title', 'VNPay Test Guide')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">🧪 Hướng dẫn Test VNPay Sandbox</h4>
                </div>
                <div class="card-body">
                    
                    <!-- Step 1: Configuration Check -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">📋 Bước 1: Kiểm tra cấu hình</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Current VNPay Config:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>TMN Code:</span>
                                            <code>{{ config('vnpay.tmn_code') }}</code>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Hash Secret:</span>
                                            <code>{{ substr(config('vnpay.hash_secret'), 0, 8) }}***</code>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Environment:</span>
                                            <span class="badge bg-warning">SANDBOX</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Test URLs:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <strong>Debug Tool:</strong><br>
                                            <a href="{{ route('debug.vnpay.index') }}" target="_blank">{{ route('debug.vnpay.index') }}</a>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Return URL:</strong><br>
                                            <code>{{ config('vnpay.return_url') }}</code>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Test Cards -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">💳 Bước 2: Thông tin thẻ test VNPay Sandbox</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <strong>Lưu ý:</strong> Đây là thẻ test cho sandbox, không có tiền thật được trừ.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>🏦 Thẻ ATM Nội địa:</h6>
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <td><strong>NCB</strong></td>
                                            <td>9704198526191432198</td>
                                            <td>NGUYEN VAN A</td>
                                            <td>07/15</td>
                                            <td>123456</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Agribank</strong></td>
                                            <td>9704052596291432198</td>
                                            <td>NGUYEN VAN A</td>
                                            <td>07/15</td>
                                            <td>123456</td>
                                        </tr>
                                        <tr>
                                            <td><strong>SCB</strong></td>
                                            <td>9704061648291432198</td>
                                            <td>NGUYEN VAN A</td>
                                            <td>07/15</td>
                                            <td>123456</td>
                                        </tr>
                                    </table>
                                    <small class="text-muted">OTP: 123456 hoặc bất kỳ 6 số nào</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>💳 Thẻ Visa/MasterCard:</h6>
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <td><strong>Visa</strong></td>
                                            <td>4000000000000002</td>
                                            <td>NGUYEN VAN A</td>
                                            <td>07/15</td>
                                            <td>123</td>
                                        </tr>
                                        <tr>
                                            <td><strong>MasterCard</strong></td>
                                            <td>5200000000000004</td>
                                            <td>NGUYEN VAN A</td>
                                            <td>07/15</td>
                                            <td>123</td>
                                        </tr>
                                    </table>
                                    <small class="text-muted">OTP: 123456</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Test Scenarios -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">🎯 Bước 3: Các kịch bản test</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">✅ Test Thành Công</h6>
                                        </div>
                                        <div class="card-body">
                                            <ol class="small">
                                                <li>Thêm sản phẩm vào cart</li>
                                                <li>Checkout → Chọn VNPay</li>
                                                <li>Nhập thông tin thẻ test</li>
                                                <li>Nhập OTP: 123456</li>
                                                <li>Xác nhận thanh toán</li>
                                            </ol>
                                            <div class="mt-2">
                                                <span class="badge bg-success">Expected: Thanh toán thành công</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-danger text-white">
                                            <h6 class="mb-0">❌ Test Thất Bại</h6>
                                        </div>
                                        <div class="card-body">
                                            <ol class="small">
                                                <li>Thêm sản phẩm vào cart</li>
                                                <li>Checkout → Chọn VNPay</li>
                                                <li>Nhập thông tin thẻ test</li>
                                                <li>Nhập OTP sai: 000000</li>
                                                <li>Hoặc hủy giao dịch</li>
                                            </ol>
                                            <div class="mt-2">
                                                <span class="badge bg-danger">Expected: Thanh toán thất bại</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">⏱️ Test Timeout</h6>
                                        </div>
                                        <div class="card-body">
                                            <ol class="small">
                                                <li>Thêm sản phẩm vào cart</li>
                                                <li>Checkout → Chọn VNPay</li>
                                                <li>Nhập thông tin thẻ</li>
                                                <li>Đợi 15 phút không làm gì</li>
                                                <li>Hoặc đóng tab VNPay</li>
                                            </ol>
                                            <div class="mt-2">
                                                <span class="badge bg-warning text-dark">Expected: Hết hạn giao dịch</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Monitoring -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">👀 Bước 4: Theo dõi và Debug</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>🔍 Debug Tools:</h6>
                                    <div class="list-group">
                                        <a href="{{ route('debug.vnpay.index') }}" class="list-group-item list-group-item-action">
                                            <strong>VNPay Debug Tool</strong><br>
                                            <small>Generate test URLs, validate hash, xem logs</small>
                                        </a>
                                        <a href="/debug/vnpay/logs" class="list-group-item list-group-item-action">
                                            <strong>Real-time Logs</strong><br>
                                            <small>Xem logs VNPay real-time</small>
                                        </a>
                                        <div class="list-group-item">
                                            <strong>Laravel Log</strong><br>
                                            <small><code>tail -f storage/logs/laravel.log | grep -i vnpay</code></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>📊 What to Check:</h6>
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <strong>Request Data:</strong> Order ID, Amount, Hash
                                        </li>
                                        <li class="list-group-item">
                                            <strong>VNPay URL:</strong> All parameters có đúng không
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Callback Data:</strong> Response code, hash validation
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Database:</strong> Order status có update không
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="text-center">
                        <h5>🚀 Quick Actions</h5>
                        <div class="btn-group-vertical d-grid gap-2 col-6 mx-auto">
                            <a href="{{ route('debug.vnpay.index') }}" class="btn btn-primary">
                                🔧 Open Debug Tool
                            </a>
                            <a href="{{ route('shop.index') }}" class="btn btn-success">
                                🛍️ Start Shopping (Test Full Flow)
                            </a>
                            <a href="{{ route('orders.my') }}" class="btn btn-info">
                                📦 View My Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
