<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VNPay Test Form</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 300px; padding: 8px; margin-bottom: 10px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #005a87; }
        .debug-info { background: #f0f0f0; padding: 15px; margin: 20px 0; border-left: 4px solid #007cba; }
    </style>
</head>
<body>
    <h2>VNPay Payment Test Form</h2>
    
    <div class="debug-info">
        <h3>Debug Information:</h3>
        <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']; ?></p>
        <p><strong>Current URL:</strong> <?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></p>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        <p><strong>Current Directory:</strong> <?php echo __DIR__; ?></p>
    </div>

    <form action="vnpay_create_payment_debug.php" method="post" id="testForm">
        <div class="form-group">
            <label for="amount">Số tiền (VND):</label>
            <input type="number" name="amount" id="amount" value="10000" min="1000" max="100000000" required>
        </div>
        
        <div class="form-group">
            <label for="language">Ngôn ngữ:</label>
            <select name="language" id="language">
                <option value="vn" selected>Tiếng Việt</option>
                <option value="en">English</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="bankCode">Phương thức thanh toán:</label>
            <select name="bankCode" id="bankCode">
                <option value="">Cổng thanh toán VNPAYQR</option>
                <option value="VNPAYQR">Thanh toán bằng ứng dụng hỗ trợ VNPAYQR</option>
                <option value="VNBANK">Thanh toán qua thẻ ATM/Tài khoản nội địa</option>
                <option value="INTCARD">Thanh toán qua thẻ quốc tế</option>
            </select>
        </div>
        
        <button type="submit">Thanh toán ngay</button>
        <button type="button" onclick="testDebug()">Test Debug</button>
    </form>

    <div class="debug-info">
        <h3>Quick Links:</h3>
        <p><a href="debug_test.php" target="_blank">Debug Test</a></p>
        <p><a href="vnpay_pay.php" target="_blank">Original VNPay Form</a></p>
        <p><a href="config.php" target="_blank">View Config</a></p>
    </div>

    <script>
        function testDebug() {
            // Open debug test in new window
            window.open('debug_test.php', '_blank');
        }

        // Form validation
        document.getElementById('testForm').addEventListener('submit', function(e) {
            const amount = document.getElementById('amount').value;
            if (amount < 1000) {
                alert('Số tiền phải ít nhất 1,000 VND');
                e.preventDefault();
                return false;
            }
            
            console.log('Form data:', {
                amount: amount,
                language: document.getElementById('language').value,
                bankCode: document.getElementById('bankCode').value
            });
        });
    </script>
</body>
</html>
