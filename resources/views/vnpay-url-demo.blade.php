<!DOCTYPE html>
<html>
<head>
    <title>VNPay URL Demo & Test</title>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .url-box { 
            font-family: monospace; 
            word-break: break-all; 
            font-size: 12px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .copy-btn { margin-top: 10px; }
        .demo-section { 
            background: #e7f3ff; 
            padding: 20px; 
            border-radius: 8px; 
            margin: 15px 0; 
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🔗 VNPay URL Demo & Signature Test</h1>
        
        <div class="demo-section">
            <h3>📋 Ví dụ VNPay URL mẫu:</h3>
            <div class="url-box" id="sampleUrl">
                https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?vnp_Version=2.1.0&vnp_Command=pay&vnp_TmnCode=K83BDEI1&vnp_Amount=10000000&vnp_CurrCode=VND&vnp_TxnRef=999&vnp_OrderInfo=Test%20Order%20%23999&vnp_OrderType=other&vnp_Locale=vn&vnp_ReturnUrl=http%3A%2F%2F127.0.0.1%3A8000%2Fvnpay%2Freturn&vnp_IpAddr=127.0.0.1&vnp_CreateDate=20250118100000&vnp_ExpireDate=20250118130000&vnp_SecureHash=abcd1234567890
            </div>
            <button class="btn btn-success copy-btn" onclick="copyToClipboard('sampleUrl')">
                📋 Copy URL Mẫu
            </button>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>🧪 Tạo URL Test Thực</h5>
                    </div>
                    <div class="card-body">
                        <button id="generateReal" class="btn btn-primary btn-lg w-100">
                            🚀 Generate Real VNPay URL
                        </button>
                        <div id="realUrlResult" class="mt-3"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5>✅ Test Signature Validation</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Paste VNPay URL để test:</label>
                            <textarea class="form-control url-box" id="testUrl" rows="4" 
                                      placeholder="Paste VNPay URL vào đây..."></textarea>
                        </div>
                        <button id="validateBtn" class="btn btn-success w-100">
                            🔍 Validate Signature
                        </button>
                        <div id="validationResult" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5>📊 Quick Tools</h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group w-100" role="group">
                            <a href="/vnpay/test-signature" class="btn btn-outline-primary">
                                🔧 Full Signature Test Tool
                            </a>
                            <a href="/vnpay/debug" class="btn btn-outline-info">
                                🐛 VNPay Debug Dashboard
                            </a>
                            <a href="/cart" class="btn btn-outline-success">
                                🛒 Test Real Checkout
                            </a>
                            <button id="quickDemo" class="btn btn-outline-warning">
                                ⚡ Quick Demo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="alert alert-info">
                <h6>💡 Hướng dẫn sử dụng:</h6>
                <ol>
                    <li><strong>Copy URL mẫu</strong> ở trên và paste vào box test</li>
                    <li><strong>Generate Real URL</strong> để tạo URL thực với signature đúng</li>
                    <li><strong>Validate</strong> để kiểm tra signature có hợp lệ không</li>
                    <li><strong>Test Real Checkout</strong> để thử nghiệm thanh toán thực tế</li>
                </ol>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            navigator.clipboard.writeText(text).then(() => {
                alert('URL đã được copy! Paste vào box test bên dưới.');
            });
        }
        
        // Generate real URL
        document.getElementById('generateReal').addEventListener('click', function() {
            this.innerHTML = '⏳ Generating...';
            
            fetch('/vnpay/debug/generate-test')
            .then(response => response.json())
            .then(data => {
                document.getElementById('realUrlResult').innerHTML = `
                    <div class="alert alert-success">
                        <h6>✅ URL Generated Successfully!</h6>
                        <div class="url-box" id="generatedUrl">${data.payment_url}</div>
                        <button class="btn btn-sm btn-success copy-btn mt-2" onclick="copyToClipboard('generatedUrl')">
                            📋 Copy Generated URL
                        </button>
                        <button class="btn btn-sm btn-primary mt-2" onclick="autoTest('${data.payment_url}')">
                            ⚡ Auto Test This URL
                        </button>
                    </div>
                `;
                this.innerHTML = '🚀 Generate Real VNPay URL';
            })
            .catch(error => {
                document.getElementById('realUrlResult').innerHTML = 
                    '<div class="alert alert-danger">❌ Error generating URL</div>';
                this.innerHTML = '🚀 Generate Real VNPay URL';
            });
        });
        
        // Validate signature
        document.getElementById('validateBtn').addEventListener('click', function() {
            const url = document.getElementById('testUrl').value.trim();
            if (!url) {
                alert('Please paste a VNPay URL first!');
                return;
            }
            
            this.innerHTML = '⏳ Validating...';
            
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
                const isValid = data.is_valid;
                document.getElementById('validationResult').innerHTML = `
                    <div class="alert alert-${isValid ? 'success' : 'danger'}">
                        <h6>${isValid ? '✅ SIGNATURE VALID' : '❌ SIGNATURE INVALID'}</h6>
                        <small><strong>Received:</strong> ${data.received_hash.substring(0, 20)}...</small><br>
                        <small><strong>Calculated:</strong> ${data.calculated_hash.substring(0, 20)}...</small>
                    </div>
                `;
                this.innerHTML = '🔍 Validate Signature';
            })
            .catch(error => {
                document.getElementById('validationResult').innerHTML = 
                    '<div class="alert alert-danger">❌ Validation error</div>';
                this.innerHTML = '🔍 Validate Signature';
            });
        });
        
        // Auto test function
        function autoTest(url) {
            document.getElementById('testUrl').value = url;
            document.getElementById('validateBtn').click();
        }
        
        // Quick demo
        document.getElementById('quickDemo').addEventListener('click', function() {
            const sampleUrl = document.getElementById('sampleUrl').textContent.trim();
            autoTest(sampleUrl);
        });
    </script>
</body>
</html>
