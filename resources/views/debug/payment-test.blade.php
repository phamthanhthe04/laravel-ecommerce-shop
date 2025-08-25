@extends('layouts.app')

@section('title', 'Payment Debug Test')

@section('content')
<div class="container py-5">
    <div class="card">
        <div class="card-header">
            <h5>Payment Debug Test</h5>
        </div>
        <div class="card-body">
            <h6>Debug Information:</h6>
            <ul>
                <li><strong>User ID:</strong> {{ auth()->id() ?? 'Not logged in' }}</li>
                <li><strong>User Role:</strong> {{ auth()->user()->role ?? 'N/A' }}</li>
                <li><strong>Current URL:</strong> {{ url()->current() }}</li>
                <li><strong>Request Method:</strong> {{ request()->method() }}</li>
                <li><strong>CSRF Token:</strong> {{ csrf_token() }}</li>
            </ul>

            @if(isset($order))
                <h6>Order Information:</h6>
                <ul>
                    <li><strong>Order ID:</strong> {{ $order->id }}</li>
                    <li><strong>Order User ID:</strong> {{ $order->user_id }}</li>
                    <li><strong>Total:</strong> {{ number_format($order->total, 0, ',', '.') }}đ</li>
                    <li><strong>Status:</strong> {{ $order->status }}</li>
                    <li><strong>Payment Status:</strong> {{ $order->payment_status }}</li>
                    <li><strong>Payment Method:</strong> {{ $order->payment_method ?? 'NULL' }}</li>
                </ul>
            @else
                <p class="text-warning">No order data available</p>
            @endif

            <div class="mt-4">
                <a href="{{ route('shop.index') }}" class="btn btn-primary">Back to Shop</a>
                @auth
                    <a href="{{ route('orders.my') }}" class="btn btn-secondary">My Orders</a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
