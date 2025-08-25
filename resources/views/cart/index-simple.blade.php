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
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cartItems as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               name="selected_products[]" 
                                               value="{{ $item['product']->id }}" 
                                               class="product-checkbox"
                                               data-price="{{ $item['subtotal'] }}">
                                    </td>
                                    <td>{{ $item['product']->name }}</td>
                                    <td>{{ $item['product']->category ? $item['product']->category->name : 'Không có' }}</td>
                                    <td>{{ number_format($item['product']->price, 0, ',', '.') }} VNĐ</td>
                                    <td>
                                        <form action="{{ route('cart.update', $item['product']->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <div class="input-group" style="width: 130px;">
                                                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="form-control quantity-input">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Cập nhật</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td>{{ number_format($item['subtotal'], 0, ',', '.') }} VNĐ</td>
                                    <td>
                                        <form action="{{ route('cart.remove', $item['product']->id) }}" method="POST" style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td colspan="5"><strong>Tổng cộng:</strong></td>
                                <td><strong>{{ number_format($total, 0, ',', '.') }} VNĐ</strong></td>
                                <td></td>
                            </tr>
                            <tr class="table-success">
                                <td colspan="5"><strong>Tổng thanh toán:</strong></td>
                                <td><strong id="selected-total">0 VNĐ</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>

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
                <div class="col-md-6 text-end">
                    @auth
                        <div class="alert alert-info mb-3">
                            <strong>Đặt hàng cho:</strong> {{ Auth::user()->name }} ({{ Auth::user()->email }})
                        </div>
                        <button type="submit" form="checkout-form" class="btn btn-success btn-lg" id="checkout-btn" disabled>
                            💳 Thanh toán (<span id="checkout-amount">0</span> VNĐ)
                        </button>
                    @else
                        <div class="alert alert-warning">
                            <p>Vui lòng <a href="{{ route('login') }}">đăng nhập</a> để thanh toán</p>
                        </div>
                    @endauth
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
        let selectedCount = 0;
        
        productCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                total += parseFloat(checkbox.dataset.price);
                selectedCount++;
            }
        });

        if (selectedTotalElement) {
            selectedTotalElement.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VNĐ';
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
            }
        });
    }

    // Khởi tạo
    updateSelectedTotal();
});
</script>
@endsection
