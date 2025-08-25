@extends('layouts.app')

@section('title', 'VNPay Test')

@section('content')
<div class="container py-5">
    <div class="card">
        <div class="card-header">
            <h5>VNPay Configuration Test</h5>
        </div>
        <div class="card-body">
            <h6>VNPay Settings:</h6>
            <ul>
                <li><strong>URL:</strong> {{ config('vnpay.url') }}</li>
                <li><strong>Return URL:</strong> {{ config('vnpay.return_url') }}</li>
                <li><strong>TMN Code:</strong> {{ config('vnpay.tmn_code') }}</li>
                <li><strong>Version:</strong> {{ config('vnpay.version') }}</li>
                <li><strong>Hash Secret:</strong> {{ substr(config('vnpay.hash_secret'), 0, 10) }}***</li>
            </ul>

            <div class="mt-4">
                <h6>Test Payment URL:</h6>
                @php
                    $testOrderId = 999;
                    $testAmount = 100000;
                    $testOrderInfo = "Test Order #999";
                    $testIp = request()->ip();
                    
                    $vnpayService = app(\App\Services\VNPayService::class);
                    $testUrl = $vnpayService->createPaymentUrl($testOrderId, $testAmount, $testOrderInfo, $testIp);
                @endphp
                
                <p><strong>Test URL Generated:</strong></p>
                <textarea class="form-control" rows="3" readonly>{{ $testUrl }}</textarea>
                
                <div class="mt-3">
                    <a href="{{ $testUrl }}" class="btn btn-primary" target="_blank">Test VNPay Payment</a>
                    <small class="text-muted d-block mt-2">
                        * Đây là URL test với đơn hàng #999, số tiền 100,000đ
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
