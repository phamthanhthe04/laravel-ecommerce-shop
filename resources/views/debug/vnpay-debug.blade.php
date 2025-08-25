@extends('layouts.app')

@section('title', 'VNPay Debug & Test Tool')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">🔍 VNPay Debug & Test Tool</h4>
                </div>
                <div class="card-body">
                    <!-- Configuration Check -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>📋 VNPay Configuration</h5>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td><strong>URL</strong></td>
                                    <td>{{ config('vnpay.url') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>TMN Code</strong></td>
                                    <td>{{ config('vnpay.tmn_code') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Return URL</strong></td>
                                    <td>{{ config('vnpay.return_url') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Hash Secret</strong></td>
                                    <td>{{ substr(config('vnpay.hash_secret'), 0, 10) }}*** ({{ strlen(config('vnpay.hash_secret')) }} chars)</td>
                                </tr>
                                <tr>
                                    <td><strong>Version</strong></td>
                                    <td>{{ config('vnpay.version') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>🌐 Environment Info</h5>
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <td><strong>App URL</strong></td>
                                    <td>{{ config('app.url') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Current IP</strong></td>
                                    <td>{{ request()->ip() }}</td>
                                </tr>
                                <tr>
                                    <td><strong>User Agent</strong></td>
                                    <td>{{ substr(request()->userAgent(), 0, 50) }}...</td>
                                </tr>
                                <tr>
                                    <td><strong>Timestamp</strong></td>
                                    <td>{{ now()->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Test Payment URL Generator -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">🧪 Test Payment URL Generator</h5>
                        </div>
                        <div class="card-body">
                            <form id="testPaymentForm">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Order ID</label>
                                        <input type="number" class="form-control" id="testOrderId" value="999" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Amount (VND)</label>
                                        <input type="number" class="form-control" id="testAmount" value="100000" min="1000">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Order Info</label>
                                        <input type="text" class="form-control" id="testOrderInfo" value="Test Payment">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-primary w-100" onclick="generateTestURL()">Generate</button>
                                    </div>
                                </div>
                            </form>
                            
                            <div id="testUrlResult" class="mt-3" style="display: none;">
                                <label class="form-label">Generated URL:</label>
                                <textarea class="form-control" id="generatedUrl" rows="3" readonly></textarea>
                                <div class="mt-2">
                                    <button class="btn btn-success" onclick="openTestPayment()">🚀 Test Payment</button>
                                    <button class="btn btn-secondary" onclick="copyToClipboard()">📋 Copy URL</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Log Viewer -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">📜 Recent VNPay Logs</h5>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-info" onclick="loadLogs()">🔄 Refresh Logs</button>
                            <div id="logContent" class="mt-3">
                                <p class="text-muted">Click "Refresh Logs" to view recent VNPay activities</p>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Validation Tool -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">🔐 Manual Hash Validation Tool</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">VNPay Response Data (JSON)</label>
                                    <textarea class="form-control" id="responseData" rows="8" placeholder='{"vnp_Amount":"10000000","vnp_BankCode":"NCB","vnp_OrderInfo":"Test Payment","vnp_ResponseCode":"00","vnp_SecureHash":"..."}'></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Validation Result</label>
                                    <div id="validationResult" class="border p-3" style="min-height: 200px; background: #f8f9fa;">
                                        <p class="text-muted">Enter VNPay response data and click validate</p>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-warning mt-2" onclick="validateHash()">🔍 Validate Hash</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Generate test payment URL
function generateTestURL() {
    const orderId = document.getElementById('testOrderId').value;
    const amount = document.getElementById('testAmount').value;
    const orderInfo = document.getElementById('testOrderInfo').value;
    
    fetch('/debug/vnpay/generate-url', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            order_id: orderId,
            amount: amount,
            order_info: orderInfo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('generatedUrl').value = data.url;
            document.getElementById('testUrlResult').style.display = 'block';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating URL');
    });
}

// Open test payment
function openTestPayment() {
    const url = document.getElementById('generatedUrl').value;
    if (url) {
        window.open(url, '_blank');
    }
}

// Copy URL to clipboard
function copyToClipboard() {
    const textarea = document.getElementById('generatedUrl');
    textarea.select();
    document.execCommand('copy');
    alert('URL copied to clipboard!');
}

// Load recent logs
function loadLogs() {
    fetch('/debug/vnpay/logs')
    .then(response => response.json())
    .then(data => {
        let logHtml = '';
        if (data.logs && data.logs.length > 0) {
            data.logs.forEach(log => {
                const levelClass = log.level === 'error' ? 'text-danger' : 
                                 log.level === 'warning' ? 'text-warning' : 'text-info';
                logHtml += `
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <span class="${levelClass} fw-bold">[${log.level.toUpperCase()}]</span>
                            <span class="text-muted small">${log.timestamp}</span>
                        </div>
                        <div>${log.message}</div>
                        ${log.context ? `<details class="mt-1"><summary class="text-muted small">Context</summary><pre class="small">${JSON.stringify(log.context, null, 2)}</pre></details>` : ''}
                    </div>
                `;
            });
        } else {
            logHtml = '<p class="text-muted">No recent VNPay logs found</p>';
        }
        document.getElementById('logContent').innerHTML = logHtml;
    })
    .catch(error => {
        document.getElementById('logContent').innerHTML = '<p class="text-danger">Error loading logs</p>';
    });
}

// Validate hash manually
function validateHash() {
    const responseData = document.getElementById('responseData').value;
    
    if (!responseData.trim()) {
        alert('Please enter VNPay response data');
        return;
    }
    
    try {
        const data = JSON.parse(responseData);
        
        fetch('/debug/vnpay/validate-hash', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            let resultHtml = `
                <div class="alert ${result.valid ? 'alert-success' : 'alert-danger'}">
                    <strong>Validation: ${result.valid ? 'VALID' : 'INVALID'}</strong>
                </div>
                <table class="table table-sm">
                    <tr><td><strong>Calculated Hash:</strong></td><td><code>${result.calculated_hash}</code></td></tr>
                    <tr><td><strong>Received Hash:</strong></td><td><code>${result.received_hash}</code></td></tr>
                    <tr><td><strong>Hash Data:</strong></td><td><code>${result.hash_data}</code></td></tr>
                </table>
            `;
            
            if (result.error) {
                resultHtml += `<div class="alert alert-warning">Error: ${result.error}</div>`;
            }
            
            document.getElementById('validationResult').innerHTML = resultHtml;
        })
        .catch(error => {
            document.getElementById('validationResult').innerHTML = 
                '<div class="alert alert-danger">Error validating hash</div>';
        });
        
    } catch (e) {
        alert('Invalid JSON format');
    }
}

// Auto refresh logs every 30 seconds
setInterval(loadLogs, 30000);
</script>
@endsection
