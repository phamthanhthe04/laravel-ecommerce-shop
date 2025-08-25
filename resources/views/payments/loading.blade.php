@extends('layouts.app')

@section('title', 'Đang xử lý thanh toán')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5>Đang chuyển hướng đến trang thanh toán</h5>
                    <p class="text-muted">Vui lòng đợi trong giây lát...</p>
                    <div class="mt-4">
                        <small class="text-muted">
                            Nếu trang không tự động chuyển hướng sau 5 giây, 
                            <a href="{{ route('payment.index', ['order_id' => request('order_id')]) }}">nhấn vào đây</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto redirect back if something goes wrong
setTimeout(function() {
    if (window.location.href.includes('loading')) {
        window.location.href = '{{ route("payment.index", ["order_id" => request("order_id")]) }}';
    }
}, 5000);
</script>
@endsection
