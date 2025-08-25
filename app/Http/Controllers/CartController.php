<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Hiển thị giỏ hàng
     */
    public function index()
    {
        $cart = Session::get('cart', []);
        $cartItems = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = Product::with('category')->find($productId);
            if ($product) {
                // Sử dụng giá đã có khuyến mãi
                $discountedPrice = $product->getDiscountedPrice();
                $subtotal = $discountedPrice * $quantity;
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
                $total += $subtotal;
            }
        }

        return view('cart.index', compact('cartItems', 'total'));
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Session::get('cart', []);
        $productId = $request->product_id;
        $quantity = $request->quantity;

        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }

        Session::put('cart', $cart);

        return redirect()->back()->with('success', 'Sản phẩm đã được thêm vào giỏ hàng!');
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     */
    public function update(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            $cart[$productId] = $request->quantity;
            Session::put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Giỏ hàng đã được cập nhật!');
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function remove($productId)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Sản phẩm đã được xóa khỏi giỏ hàng!');
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear()
    {
        Session::forget('cart');
        return redirect()->route('cart.index')->with('success', 'Giỏ hàng đã được xóa!');
    }

    /**
     * Checkout - tạo đơn hàng từ giỏ hàng
     */
    public function checkout(Request $request)
    {
        // Thêm logging để debug
        Log::info('Cart checkout called', [
            'method' => $request->method(),
            'url' => $request->url(),
            'all_data' => $request->all(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip()
        ]);

        // Chấp nhận cả POST và PUT (có thể do method spoofing)
        if (!in_array($request->method(), ['POST', 'PUT'])) {
            Log::error('Wrong method for checkout', [
                'method' => $request->method(),
                'expected' => 'POST or PUT',
                'url' => $request->url()
            ]);
            
            // Redirect thay vì trả về JSON error
            return redirect()->route('cart.index')
                ->with('error', 'Phương thức không được phép. Vui lòng thử lại.');
        }

        try {
            // Kiểm tra user đã đăng nhập
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán');
            }

            $cart = Session::get('cart', []);
            
            if (empty($cart)) {
                return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống!');
            }

            // Kiểm tra sản phẩm được chọn
            $selectedProducts = $request->input('selected_products', []);
            
            if (empty($selectedProducts)) {
                return redirect()->route('cart.index')->with('error', 'Vui lòng chọn ít nhất một sản phẩm để thanh toán!');
            }

            // Validate thông tin giao hàng
            $request->validate([
                'delivery_name' => 'required|string|max:255',
                'delivery_phone' => 'required|string|max:20',
                'delivery_address' => 'required|string|max:500',
                'delivery_ward' => 'nullable|string|max:100',
                'delivery_district' => 'nullable|string|max:100', 
                'delivery_province' => 'nullable|string|max:100',
                'delivery_note' => 'nullable|string|max:500'
            ], [
                'delivery_name.required' => 'Vui lòng nhập tên người nhận',
                'delivery_phone.required' => 'Vui lòng nhập số điện thoại người nhận',
                'delivery_address.required' => 'Vui lòng nhập địa chỉ giao hàng'
            ]);

            $currentUser = Auth::user();

            // Tạo đơn hàng với user hiện tại và thông tin giao hàng
            $order = Order::create([
                'user_id' => $currentUser->id,
                'total' => 0,
                'status' => 'pending',
                'payment_status' => 'pending',
                'delivery_name' => $request->delivery_name,
                'delivery_phone' => $request->delivery_phone,
                'delivery_address' => $request->delivery_address,
                'delivery_ward' => $request->delivery_ward,
                'delivery_district' => $request->delivery_district,
                'delivery_province' => $request->delivery_province,
                'delivery_note' => $request->delivery_note
            ]);

            $total = 0;

            // Thêm các sản phẩm được chọn từ giỏ hàng vào đơn hàng
            foreach ($selectedProducts as $productId) {
                if (isset($cart[$productId])) {
                    $quantity = $cart[$productId];
                    $product = Product::find($productId);
                    if ($product) {
                        // Kiểm tra tồn kho
                        if (isset($product->stock_quantity) && $product->stock_quantity < $quantity) {
                            // Xóa đơn hàng nếu không đủ hàng
                            $order->delete();
                            return redirect()->route('cart.index')
                                ->with('error', "Sản phẩm '{$product->name}' chỉ còn {$product->stock_quantity} trong kho, không đủ cho yêu cầu {$quantity} sản phẩm.");
                        }

                        $orderItem = $order->orderItems()->create([
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'price' => $product->getDiscountedPrice() // Sử dụng giá có khuyến mãi
                        ]);

                        // Log để debug
                        Log::info('Order item created', [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'original_price' => $product->price,
                            'discounted_price' => $product->getDiscountedPrice(),
                            'quantity' => $quantity,
                            'subtotal' => $orderItem->price * $orderItem->quantity
                        ]);

                        $total += $orderItem->price * $orderItem->quantity;
                        
                        // Xóa sản phẩm đã thanh toán khỏi giỏ hàng
                        unset($cart[$productId]);
                    }
                }
            }

            // Cập nhật tổng tiền đơn hàng
            $order->update(['total' => $total]);

            // Log tạo đơn hàng
            Log::info('Order created from cart', [
                'order_id' => $order->id,
                'user_id' => $currentUser->id,
                'user_name' => $currentUser->name,
                'total' => $total,
                'items_count' => count($selectedProducts)
            ]);

            // Cập nhật giỏ hàng (chỉ xóa sản phẩm đã thanh toán)
            Session::put('cart', $cart);

            return redirect()->route('payment.index', ['order_id' => $order->id])
                             ->with('success', "Đơn hàng #{$order->id} đã được tạo thành công cho {$currentUser->name}! Vui lòng chọn phương thức thanh toán.");
                             
        } catch (\Exception $e) {
            Log::error('Cart checkout error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('cart.index')
                ->with('error', 'Có lỗi xảy ra trong quá trình tạo đơn hàng. Vui lòng thử lại.');
        }
    }

    /**
     * Đếm số lượng sản phẩm trong giỏ hàng
     */
    public function count()
    {
        $cart = Session::get('cart', []);
        return response()->json(['count' => array_sum($cart)]);
    }
}