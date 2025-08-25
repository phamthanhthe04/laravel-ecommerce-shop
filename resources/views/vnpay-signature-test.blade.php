<!DOCTYPE html>
<html>
<head>
    <title>VNPay Signature Test</title>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hash-preview { font-family: monospace; word-break: break-all; }
        .signature-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .test-url { font-size: 12px; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>VNPay Signature Validation Test</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Test Parameters</h5>
                    </div>
                    <div class="card-body">
                        <small>Secret Key: {{ $secret_preview }}</small>
                        <table class="table table-sm">
                            @foreach($test_params as $key => $value)
                            <tr>
                                <td><code>{{ $key }}</code></td>
                                <td>{{ $value }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>URL Validation Test</h5>
                    </div>
                    <div class="card-body">
                        <form id="urlTestForm">
                            <div class="mb-3">
                                <label>Test URL:</label>
                                <textarea class="form-control test-url" id="testUrl" rows="3" placeholder="Paste VNPay URL here..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Validate URL</button>
                        </form>
                        
                        <div id="urlTestResult" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            @foreach($results as $method => $result)
            <div class="col-md-4">
                <div class="signature-box">
                    <h6>{{ $result['description'] }}</h6>
                    
                    <div class="mb-2">
                        <small><strong>Hash Data:</strong></small>
                        <div class="hash-preview" style="font-size: 10px; max-height: 100px; overflow-y: auto;">
                            {{ $result['hash_data'] }}
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <small><strong>Signature:</strong></small>
                        <div class="hash-preview" style="font-size: 10px;">
                            {{ substr($result['signature'], 0, 20) }}...
                        </div>
                    </div>
                    
                    <button class="btn btn-sm btn-outline-secondary copy-signature" 
                            data-signature="{{ $result['signature'] }}">
                        Copy Full Signature
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Live Test URLs</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <button id="generateTestUrl" class="btn btn-success">Generate Test Payment URL</button>
                            <button id="checkLastOrder" class="btn btn-info">Check Last Order Status</button>
                        </div>
                        <div id="testUrlResult"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Copy signature functionality
        document.querySelectorAll('.copy-signature').forEach(button => {
            button.addEventListener('click', function() {
                const signature = this.getAttribute('data-signature');
                navigator.clipboard.writeText(signature).then(() => {
                    this.textContent = 'Copied!';
                    setTimeout(() => {
                        this.textContent = 'Copy Full Signature';
                    }, 2000);
                });
            });
        });
        
        // URL validation test
        document.getElementById('urlTestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const url = document.getElementById('testUrl').value;
            
            if (!url) {
                alert('Please enter a URL');
                return;
            }
            
            fetch('/vnpay/test-signature/validate-url', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ test_url: url })
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('urlTestResult');
                const isValid = data.is_valid;
                
                resultDiv.innerHTML = `
                    <div class="alert alert-${isValid ? 'success' : 'danger'}">
                        <h6>Validation Result: ${isValid ? 'VALID' : 'INVALID'}</h6>
                        <small><strong>Received:</strong> ${data.received_hash.substring(0, 20)}...</small><br>
                        <small><strong>Calculated:</strong> ${data.calculated_hash.substring(0, 20)}...</small><br>
                        <small><strong>Hash Data:</strong> ${data.hash_data.substring(0, 100)}...</small>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('urlTestResult').innerHTML = 
                    '<div class="alert alert-danger">Error validating URL</div>';
            });
        });
        
        // Generate test URL
        document.getElementById('generateTestUrl').addEventListener('click', function() {
            fetch('/vnpay/debug/generate-test')
            .then(response => response.json())
            .then(data => {
                document.getElementById('testUrlResult').innerHTML = `
                    <div class="alert alert-info">
                        <h6>Generated Test URL:</h6>
                        <textarea class="form-control test-url" rows="3" readonly>${data.payment_url}</textarea>
                        <button class="btn btn-sm btn-success mt-2" onclick="document.getElementById('testUrl').value = '${data.payment_url}'; document.getElementById('urlTestForm').dispatchEvent(new Event('submit'));">
                            Auto-validate This URL
                        </button>
                    </div>
                `;
            });
        });
        
        // Check last order
        document.getElementById('checkLastOrder').addEventListener('click', function() {
            fetch('/api/orders/latest')
            .then(response => response.json())
            .then(data => {
                document.getElementById('testUrlResult').innerHTML = `
                    <div class="alert alert-warning">
                        <h6>Last Order: #${data.id}</h6>
                        <p>Status: ${data.payment_status} | Total: ${data.total}₫</p>
                        <p>Created: ${data.created_at}</p>
                    </div>
                `;
            });
        });
    </script>
</body>
</html>
