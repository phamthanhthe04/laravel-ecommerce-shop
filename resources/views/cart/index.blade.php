@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/simple-cart.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container">
    <div class="cart-container">
        <div class="row">
            <div class="col-12">
                <h2 class="cart-title">🛒 Giỏ hàng</h2>
                <div class="mb-3">
                    <a class="btn btn-primary" href="{{ route('shop.index') }}">
                        ← Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>

        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                {{ $message }}
            </div>
        @endif

        @if ($message = Session::get('error'))
            <div class="alert alert-danger">
                {{ $message }}
            </div>
        @endif

        @if (!empty($cartItems))
            <div class="cart-instruction">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Hướng dẫn:</strong> Tích chọn các sản phẩm bạn muốn thanh toán, sau đó nhấn nút "Thanh toán". 
                Các sản phẩm không được chọn sẽ vẫn ở lại trong giỏ hàng.
            </div>

            <form id="checkout-form" action="{{ route('cart.checkout') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="50px">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th>Sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá gốc</th>
                                <th>Khuyến mãi</th>
                                <th>Giá bán</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cartItems as $item)
                                @php
                                    $product = $item['product'];
                                    $activePromotion = $product->getBestActivePromotion();
                                    $originalPrice = $product->price;
                                    $discountedPrice = $product->getDiscountedPrice();
                                    $subtotal = $discountedPrice * $item['quantity'];
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               name="selected_products[]" 
                                               value="{{ $product->id }}" 
                                               class="product-checkbox"
                                               data-price="{{ $subtotal }}"
                                               data-original-price="{{ $originalPrice * $item['quantity'] }}">
                                    </td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        @if($activePromotion)
                                            <br><small class="text-success">
                                                <i class="fas fa-gift"></i> {{ $activePromotion->name }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $product->category ? $product->category->name : 'Không có' }}</td>
                                    <td>
                                        <span {{ $activePromotion ? 'class=text-muted text-decoration-line-through' : '' }}>
                                            {{ number_format($originalPrice, 0, ',', '.') }} VNĐ
                                        </span>
                                    </td>
                                    <td>
                                        @if($activePromotion)
                                            @if($activePromotion->discount_type === 'percentage')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-percentage me-1"></i>-{{ $activePromotion->discount_value }}%
                                                </span>
                                                <br><small class="text-success">
                                                    Tiết kiệm: {{ number_format($originalPrice - $discountedPrice, 0, ',', '.') }}đ
                                                </small>
                                            @else
                                                <span class="badge bg-info text-white">
                                                    <i class="fas fa-minus-circle me-1"></i>-{{ number_format($activePromotion->discount_value, 0, ',', '.') }}đ
                                                </span>
                                                <br><small class="text-success">
                                                    Tiết kiệm: {{ number_format($originalPrice - $discountedPrice, 0, ',', '.') }}đ
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">Không có</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activePromotion)
                                            <strong class="text-danger">{{ number_format($discountedPrice, 0, ',', '.') }} VNĐ</strong>
                                        @else
                                            {{ number_format($originalPrice, 0, ',', '.') }} VNĐ
                                        @endif
                                    </td>
                                    <td>
                                        <div class="input-group" style="width: 130px;">
                                            <input type="number" 
                                                   value="{{ $item['quantity'] }}" 
                                                   min="1" 
                                                   class="form-control quantity-input"
                                                   data-product-id="{{ $product->id }}">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary update-quantity-btn"
                                                    data-product-id="{{ $product->id }}">
                                                Cập nhật
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        @if($activePromotion)
                                            <div>
                                                <span class="text-muted text-decoration-line-through small">
                                                    {{ number_format($originalPrice * $item['quantity'], 0, ',', '.') }} VNĐ
                                                </span>
                                            </div>
                                            <strong class="text-danger">{{ number_format($subtotal, 0, ',', '.') }} VNĐ</strong>
                                        @else
                                            <strong>{{ number_format($subtotal, 0, ',', '.') }} VNĐ</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger remove-item-btn" 
                                                data-product-id="{{ $product->id }}"
                                                onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                            Xóa
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="6"><strong>Tổng cộng (giá gốc):</strong></td>
                                <td><strong id="original-total">{{ number_format($total, 0, ',', '.') }} VNĐ</strong></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr class="table-warning">
                                <td colspan="6"><strong>Tổng tiết kiệm:</strong></td>
                                <td><strong id="total-savings" class="text-success">0 VNĐ</strong></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr class="table-success">
                                <td colspan="6"><strong>Tổng thanh toán:</strong></td>
                                <td><strong id="selected-total">0 VNĐ</strong></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Thông tin giao hàng -->
                @auth
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-truck me-2"></i>
                                    Thông tin giao hàng
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="delivery_name" class="form-label">
                                                <i class="fas fa-user me-1"></i>
                                                Tên người nhận <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="delivery_name" 
                                                   name="delivery_name" 
                                                   value="{{ Auth::user()->name }}" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="delivery_phone" class="form-label">
                                                <i class="fas fa-phone me-1"></i>
                                                Số điện thoại <span class="text-danger">*</span>
                                            </label>
                                            <input type="tel" 
                                                   class="form-control" 
                                                   id="delivery_phone" 
                                                   name="delivery_phone" 
                                                   value="{{ Auth::user()->phone ?? '' }}" 
                                                   required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="delivery_province" class="form-label">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                Tỉnh/Thành phố
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="delivery_province" 
                                                   name="delivery_province" 
                                                   placeholder="VD: TP.HCM, Hà Nội">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="delivery_district" class="form-label">
                                                <i class="fas fa-map me-1"></i>
                                                Quận/Huyện
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="delivery_district" 
                                                   name="delivery_district" 
                                                   placeholder="VD: Quận 1, Huyện Gia Lâm">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="delivery_ward" class="form-label">
                                                <i class="fas fa-location-arrow me-1"></i>
                                                Phường/Xã
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="delivery_ward" 
                                                   name="delivery_ward" 
                                                   placeholder="VD: Phường Bến Nghé">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="delivery_address" class="form-label">
                                        <i class="fas fa-home me-1"></i>
                                        Địa chỉ cụ thể <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="delivery_address" 
                                           name="delivery_address" 
                                           placeholder="Số nhà, tên đường..." 
                                           required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="delivery_note" class="form-label">
                                        <i class="fas fa-sticky-note me-1"></i>
                                        Ghi chú giao hàng
                                    </label>
                                    <textarea class="form-control" 
                                              id="delivery_note" 
                                              name="delivery_note" 
                                              rows="2" 
                                              placeholder="Ghi chú thêm cho người giao hàng (tùy chọn)"></textarea>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Lưu ý:</strong> Vui lòng kiểm tra kỹ thông tin giao hàng trước khi đặt hàng. 
                                    Thông tin này sẽ được sử dụng để giao hàng đến địa chỉ của bạn.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <!-- Clear cart form outside main form -->
                    </div>
                    <div class="col-md-6 text-end">
                        @auth
                            <div class="alert alert-info mb-3">
                                <strong>Đặt hàng cho:</strong> {{ Auth::user()->name }} ({{ Auth::user()->email }})
                            </div>
                            <button type="submit" class="btn btn-success btn-lg" id="checkout-btn" disabled>
                                💳 Thanh toán (<span id="checkout-amount">0</span> VNĐ)
                            </button>
                        @else
                            <div class="alert alert-warning">
                                <p>Vui lòng <a href="{{ route('login') }}">đăng nhập</a> để thanh toán</p>
                            </div>
                        @endauth
                    </div>
                </div>
            </form>

            <!-- Clear cart form moved outside -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <form action="{{ route('cart.clear') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-warning" 
                                onclick="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')">
                            🗑️ Xóa toàn bộ giỏ hàng
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center">
                <h4>Giỏ hàng trống!</h4>
                <p>Hãy thêm một số sản phẩm vào giỏ hàng.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary">Đi mua sắm</a>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const selectedTotalElement = document.getElementById('selected-total');
    const checkoutBtn = document.getElementById('checkout-btn');
    const checkoutAmount = document.getElementById('checkout-amount');

    function updateSelectedTotal() {
        let total = 0;
        let originalTotal = 0;
        let selectedCount = 0;
        
        productCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                total += parseFloat(checkbox.dataset.price);
                originalTotal += parseFloat(checkbox.dataset.originalPrice || checkbox.dataset.price);
                selectedCount++;
            }
        });

        const totalSavings = originalTotal - total;

        if (selectedTotalElement) {
            selectedTotalElement.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VNĐ';
        }
        
        const totalSavingsElement = document.getElementById('total-savings');
        if (totalSavingsElement) {
            totalSavingsElement.textContent = new Intl.NumberFormat('vi-VN').format(totalSavings) + ' VNĐ';
        }
        
        if (checkoutAmount) {
            checkoutAmount.textContent = new Intl.NumberFormat('vi-VN').format(total);
        }
        
        if (checkoutBtn) {
            checkoutBtn.disabled = selectedCount === 0;
        }
    }

    // Xử lý checkbox "Chọn tất cả"
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                const row = checkbox.closest('tr');
                if (this.checked) {
                    row.classList.add('selected');
                } else {
                    row.classList.remove('selected');
                }
            });
            updateSelectedTotal();
        });
    }

    // Xử lý các checkbox sản phẩm
    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.checked) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
            
            updateSelectedTotal();
            
            // Cập nhật trạng thái checkbox "Chọn tất cả"
            if (selectAllCheckbox) {
                const allChecked = Array.from(productCheckboxes).every(cb => cb.checked);
                const noneChecked = Array.from(productCheckboxes).every(cb => !cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
            }
        });
    });

    // Xử lý form checkout
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một sản phẩm để thanh toán!');
                return;
            }
            
            // Validate thông tin giao hàng
            const deliveryName = document.getElementById('delivery_name');
            const deliveryPhone = document.getElementById('delivery_phone');
            const deliveryAddress = document.getElementById('delivery_address');
            
            if (deliveryName && !deliveryName.value.trim()) {
                e.preventDefault();
                deliveryName.focus();
                alert('Vui lòng nhập tên người nhận!');
                return;
            }
            
            if (deliveryPhone && !deliveryPhone.value.trim()) {
                e.preventDefault();
                deliveryPhone.focus();
                alert('Vui lòng nhập số điện thoại người nhận!');
                return;
            }
            
            if (deliveryAddress && !deliveryAddress.value.trim()) {
                e.preventDefault();
                deliveryAddress.focus();
                alert('Vui lòng nhập địa chỉ giao hàng!');
                return;
            }
            
            // Debug: Log form data before submit
            console.log('Form method:', this.method);
            console.log('Form action:', this.action);
            console.log('Form elements:', Array.from(new FormData(this)).map(([name, value]) => ({name, value})));
        });
    }

    // Xử lý cập nhật số lượng
    document.querySelectorAll('.update-quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantityInput = document.querySelector(`input[data-product-id="${productId}"]`);
            const quantity = quantityInput.value;
            
            if (quantity < 1) {
                alert('Số lượng phải lớn hơn 0');
                return;
            }
            
            // Tạo form động để submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/cart/update/${productId}`;
            
            // Thêm CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);
            
            // Thêm method
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);
            
            // Thêm quantity
            const quantityInputHidden = document.createElement('input');
            quantityInputHidden.type = 'hidden';
            quantityInputHidden.name = 'quantity';
            quantityInputHidden.value = quantity;
            form.appendChild(quantityInputHidden);
            
            document.body.appendChild(form);
            form.submit();
        });
    });
    
    // Xử lý xóa sản phẩm
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            // Tạo form động để submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/cart/remove/${productId}`;
            
            // Thêm CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);
            
            // Thêm method
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        });
    });

    // Khởi tạo
    updateSelectedTotal();
});
</script>
@endsection
