@extends('layouts.app')

@section('title', 'Mock Payment Gateway')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Payment Gateway Card -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Mock Payment Gateway
                    </h4>
                    <small>🧪 Test Mode - Simulation Payment</small>
                </div>
                <div class="card-body">
                    <!-- Alert Test Mode -->
                    <div class="alert alert-warning border-0 mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Test Mode:</strong> This is a mock payment gateway for testing purposes. No real money
                        will be charged.
                    </div>

                    <!-- Order Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-receipt me-2"></i>Order Details
                                    </h6>
                                    <p class="mb-1"><strong>Order ID:</strong> #{{ $order->id }}</p>
                                    <p class="mb-1"><strong>Customer:</strong> {{ $order->user->name }}</p>
                                    <p class="mb-1"><strong>Date:</strong> {{ $order->created_at->format('d/m/Y H:i') }}
                                    </p>
                                    <p class="mb-0"><strong>Total Amount:</strong>
                                        <span
                                            class="text-primary fw-bold">{{ number_format($order->total, 0, ',', '.') }}đ</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-box me-2"></i>Items ({{ $order->orderItems->count() }})
                                    </h6>
                                    @foreach($order->orderItems->take(3) as $item)
                                    <p class="mb-1 small">
                                        <span class="badge bg-secondary">{{ $item->quantity }}x</span>
                                        {{ Str::limit($item->product->name, 25) }}
                                    </p>
                                    @endforeach
                                    @if($order->orderItems->count() > 3)
                                    <p class="mb-0 small text-muted">
                                        ... and {{ $order->orderItems->count() - 3 }} more items
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Test Buttons -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6><i class="fas fa-bolt me-2"></i>Quick Test</h6>
                            <p class="text-muted small">Click to simulate payment result instantly</p>
                            <div class="d-flex gap-3 flex-wrap">
                                <button type="button" class="btn btn-success" onclick="quickPayment('success')">
                                    <i class="fas fa-check me-2"></i>✅ Simulate Success
                                </button>
                                <button type="button" class="btn btn-danger" onclick="quickPayment('fail')">
                                    <i class="fas fa-times me-2"></i>❌ Simulate Failure
                                </button>
                                <button type="button" class="btn btn-warning" onclick="randomPayment()">
                                    <i class="fas fa-dice me-2"></i>🎲 Random Result
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Detailed Payment Form -->
                    <div class="detailed-form">
                        <h6><i class="fas fa-form me-2"></i>Detailed Payment Simulation</h6>
                        <p class="text-muted small">Fill the form to simulate a more realistic payment flow</p>

                        <form action="{{ route('mock.payment.process') }}" method="POST" id="mockPaymentForm">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Card Number</label>
                                        <input type="text" class="form-control" name="card_number"
                                            placeholder="1234 5678 9012 3456" required maxlength="19" id="cardNumber">
                                        <div class="form-text">Use any 16-digit number for testing</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Cardholder Name</label>
                                        <input type="text" class="form-control" name="cardholder_name"
                                            placeholder="JOHN DOE" required
                                            value="{{ strtoupper($order->user->name) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" placeholder="MM/YY" maxlength="5"
                                            id="expiryDate">
                                        <div class="form-text">Any future date</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" placeholder="123" maxlength="4"
                                            id="cvv">
                                        <div class="form-text">Any 3-4 digits</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Test Result</label>
                                        <select class="form-select" name="action" required>
                                            <option value="">Choose result...</option>
                                            <option value="success">✅ Success</option>
                                            <option value="fail">❌ Failure</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="{{ route('payment.index', ['order_id' => $order->id]) }}"
                                    class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Payment Options
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-credit-card me-2"></i>Process Mock Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Test Cards Info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Test Card Numbers</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Visa:</strong></p>
                            <code>4111 1111 1111 1111</code>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Mastercard:</strong></p>
                            <code>5555 5555 5555 4444</code>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>American Express:</strong></p>
                            <code>3782 8224 6310 005</code>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        These are test card numbers. No real transactions will be processed.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Quick Payment Function
function quickPayment(result) {
    if (confirm(`Simulate ${result} payment for Order #{{ $order->id }}?`)) {
        fetch('{{ route("mock.payment.quick") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    order_id: {{ $order->id }},
                    result: result
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message + '\nTransaction ID: ' + data.transaction_id);
                    window.location.href = '{{ route("orders.show", $order->id) }}';
                } else {
                    alert('❌ ' + data.message + '\nTransaction ID: ' + data.transaction_id);
                    window.location.href = '{{ route("orders.show", $order->id) }}';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing payment');
            });
    }
}

// Random Payment Result
function randomPayment() {
    const results = ['success', 'fail'];
    const randomResult = results[Math.floor(Math.random() * results.length)];
    quickPayment(randomResult);
}

// Format card number
document.getElementById('cardNumber').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let groups = value.match(/.{1,4}/g);
    let formattedValue = groups ? groups.join(' ') : value;
    e.target.value = formattedValue;
});

// Format expiry date
document.getElementById('expiryDate').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});

// Only numbers for CVV
document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});
</script>
@endsection