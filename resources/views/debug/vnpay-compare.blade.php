<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNPay Implementation Comparison</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .diff-highlight { background-color: #ffeb3b; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>VNPay Implementation Comparison</h1>
        
        <div class="alert alert-info">
            <strong>Test Data:</strong><br>
            Order ID: {{ $test_data['order_id'] }}<br>
            Amount: {{ number_format($test_data['amount']) }} VND<br>
            Order Info: {{ $test_data['order_info'] }}
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Controller Implementation</h3>
                    </div>
                    <div class="card-body">
                        @if(isset($controller_result['error']))
                            <div class="alert alert-danger">{{ $controller_result['error'] }}</div>
                        @else
                            <h5>Input Data:</h5>
                            <pre>{{ json_encode($controller_result['input_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            
                            <h5>Hash Data:</h5>
                            <pre>{{ $controller_result['hash_data'] }}</pre>
                            
                            <h5>Secure Hash:</h5>
                            <pre>{{ $controller_result['secure_hash'] }}</pre>
                            
                            <h5>Final URL:</h5>
                            <pre>{{ $controller_result['url'] }}</pre>
                            
                            <a href="{{ $controller_result['url'] }}" target="_blank" class="btn btn-primary">Test Payment</a>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Service Implementation</h3>
                    </div>
                    <div class="card-body">
                        @if(is_string($service_result))
                            <h5>Service URL:</h5>
                            <pre>{{ $service_result }}</pre>
                            <a href="{{ $service_result }}" target="_blank" class="btn btn-success">Test Payment</a>
                        @else
                            <div class="alert alert-danger">Service returned non-URL result</div>
                            <pre>{{ print_r($service_result, true) }}</pre>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Comparison Result</h3>
                    </div>
                    <div class="card-body">
                        @if(isset($controller_result['url']) && is_string($service_result))
                            @if($controller_result['url'] === $service_result)
                                <div class="alert alert-success">
                                    <strong>✓ URLs Match!</strong> Both implementations generate the same URL.
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <strong>⚠ URLs Different!</strong> The implementations generate different URLs.
                                </div>
                                
                                <h5>URL Differences:</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Controller URL Length:</strong> {{ strlen($controller_result['url']) }}<br>
                                        <strong>Service URL Length:</strong> {{ strlen($service_result) }}
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-danger">
                                <strong>✗ Cannot Compare!</strong> One or both implementations failed.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('vnpay.sandbox.form') }}" class="btn btn-secondary">Back to Sandbox</a>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary">Home</a>
        </div>
    </div>
</body>
</html>
