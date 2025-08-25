@extends('layouts.app')

@section('title', 'VNPay Callback Monitor')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">📡 VNPay Callback Monitor - Order #32</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>📊 Order Info từ Log:</h5>
                            <table class="table table-bordered">
                                <tr><td><strong>Order ID</strong></td><td>32</td></tr>
                                <tr><td><strong>User</strong></td><td>Admin (ID: 1)</td></tr>
                                <tr><td><strong>Amount</strong></td><td>25,990,000 VND</td></tr>
                                <tr><td><strong>VNPay Amount</strong></td><td>2,599,000,000 (x100)</td></tr>
                                <tr><td><strong>Status</strong></td><td><span id="orderStatus">Checking...</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>🔗 Generated URL Info:</h5>
                            <table class="table table-bordered">
                                <tr><td><strong>URL Length</strong></td><td>546 chars</td></tr>
                                <tr><td><strong>Parameters</strong></td><td>13 params</td></tr>
                                <tr><td><strong>Hash</strong></td><td>eb5d03b1ee...</td></tr>
                                <tr><td><strong>Expire</strong></td><td>20250818003153</td></tr>
                                <tr><td><strong>Return URL</strong></td><td>✅ Configured</td></tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-8">
                            <h5>📜 Real-time Callback Monitor:</h5>
                            <div id="callbackLog" class="border p-3 bg-light" style="height: 400px; overflow-y: auto;">
                                <p class="text-muted">Waiting for VNPay callback...</p>
                                <p><small>Make payment on VNPay page to see callback data here</small></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5>🎯 Test Actions:</h5>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="checkOrderStatus()">
                                    🔄 Check Order Status
                                </button>
                                <button class="btn btn-success" onclick="startMonitoring()">
                                    ▶️ Start Monitoring
                                </button>
                                <button class="btn btn-warning" onclick="stopMonitoring()">
                                    ⏹️ Stop Monitoring
                                </button>
                                <button class="btn btn-info" onclick="clearLog()">
                                    🗑️ Clear Log
                                </button>
                                <a href="{{ route('orders.show', 32) }}" class="btn btn-outline-primary">
                                    📦 View Order #32
                                </a>
                            </div>

                            <div class="mt-3">
                                <h6>💳 Test Cards:</h6>
                                <div class="card">
                                    <div class="card-body small">
                                        <strong>NCB ATM:</strong><br>
                                        9704198526191432198<br>
                                        Exp: 07/15, OTP: 123456<br><br>
                                        
                                        <strong>Visa:</strong><br>
                                        4000000000000002<br>
                                        Exp: 07/15, CVV: 123
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let monitoring = false;
let monitorInterval;

function checkOrderStatus() {
    fetch('/api/order/32/status')
    .then(response => response.json())
    .then(data => {
        const statusElement = document.getElementById('orderStatus');
        const statusClass = data.payment_status === 'paid' ? 'text-success' : 
                           data.payment_status === 'failed' ? 'text-danger' : 'text-warning';
        statusElement.innerHTML = `<span class="${statusClass}">${data.payment_status.toUpperCase()}</span>`;
        
        addToLog(`Order Status: ${data.payment_status} | Total: ${data.total} VND`, 'info');
    })
    .catch(error => {
        addToLog('Error checking order status: ' + error, 'error');
    });
}

function startMonitoring() {
    if (monitoring) return;
    
    monitoring = true;
    addToLog('🟢 Monitoring started - waiting for VNPay callback...', 'success');
    
    monitorInterval = setInterval(() => {
        fetch('/debug/vnpay/logs')
        .then(response => response.json())
        .then(data => {
            if (data.logs && data.logs.length > 0) {
                data.logs.forEach(log => {
                    if (log.message.includes('VNPay') || log.message.includes('32')) {
                        const timestamp = new Date(log.timestamp).toLocaleTimeString();
                        addToLog(`[${timestamp}] ${log.message}`, log.level);
                    }
                });
            }
        })
        .catch(error => console.error('Monitor error:', error));
    }, 2000); // Check every 2 seconds
}

function stopMonitoring() {
    monitoring = false;
    if (monitorInterval) {
        clearInterval(monitorInterval);
    }
    addToLog('🔴 Monitoring stopped', 'warning');
}

function clearLog() {
    document.getElementById('callbackLog').innerHTML = 
        '<p class="text-muted">Log cleared. Ready for new data...</p>';
}

function addToLog(message, level = 'info') {
    const logDiv = document.getElementById('callbackLog');
    const timestamp = new Date().toLocaleTimeString();
    
    const levelClass = level === 'error' ? 'text-danger' : 
                      level === 'success' ? 'text-success' : 
                      level === 'warning' ? 'text-warning' : 'text-info';
    
    const logEntry = document.createElement('div');
    logEntry.className = 'border-bottom pb-1 mb-1';
    logEntry.innerHTML = `
        <span class="text-muted small">[${timestamp}]</span>
        <span class="${levelClass}">${message}</span>
    `;
    
    logDiv.appendChild(logEntry);
    logDiv.scrollTop = logDiv.scrollHeight; // Auto scroll to bottom
}

// Auto check order status when page loads
document.addEventListener('DOMContentLoaded', function() {
    checkOrderStatus();
    
    // Show live URL from log
    addToLog('Order #32 payment URL generated successfully', 'success');
    addToLog('Amount: 25,990,000 VND → VNPay format: 2,599,000,000', 'info');
    addToLog('Hash: eb5d03b1ee... ✅', 'info');
    addToLog('Ready to receive callback from VNPay', 'info');
});

// Cleanup on page unload
window.addEventListener('beforeunload', stopMonitoring);
</script>
@endsection
