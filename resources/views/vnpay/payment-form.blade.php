<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNPay Sandbox Payment Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>VNPay Sandbox Payment Test</h4>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('vnpay.sandbox.create') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="amount" class="form-label">Số tiền (VND)</label>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       value="10000" min="1000" max="100000000" required>
                                @error('amount')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phương thức thanh toán</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="bankCode" value="" id="bankCode1" checked>
                                    <label class="form-check-label" for="bankCode1">
                                        Cổng thanh toán VNPAYQR
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="bankCode" value="VNPAYQR" id="bankCode2">
                                    <label class="form-check-label" for="bankCode2">
                                        Thanh toán bằng ứng dụng hỗ trợ VNPAYQR
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="bankCode" value="VNBANK" id="bankCode3">
                                    <label class="form-check-label" for="bankCode3">
                                        Thanh toán qua thẻ ATM/Tài khoản nội địa
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="bankCode" value="INTCARD" id="bankCode4">
                                    <label class="form-check-label" for="bankCode4">
                                        Thanh toán qua thẻ quốc tế
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ngôn ngữ</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="language" value="vn" id="lang1" checked>
                                    <label class="form-check-label" for="lang1">Tiếng Việt</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="language" value="en" id="lang2">
                                    <label class="form-check-label" for="lang2">English</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Thanh toán ngay</button>
                        </form>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Debug Links</h5>
                    </div>
                    <div class="card-body">
                        <p><a href="{{ url('/vnpay_php/vnpay_pay.php') }}" target="_blank" class="btn btn-outline-secondary btn-sm">VNPay PHP Files</a></p>
                        <p><a href="{{ url('/vnpay_php/test_form.php') }}" target="_blank" class="btn btn-outline-secondary btn-sm">PHP Debug Form</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
