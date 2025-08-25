<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\VNPaySandboxService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $vnPayService;
    protected $inventoryService;

    public function __construct(VNPaySandboxService $vnPayService, InventoryService $inventoryService)
    {
        $this->vnPayService = $vnPayService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Hiển thị trang chọn phương thức thanh toán
     */
    public function index(Request $request)
    {
        try {
            $orderId = $request->query('order_id');
            
            if (!$orderId) {
                return redirect()->route('shop.index')->with('error', 'Không tìm thấy đơn hàng');
            }
            
            $order = Order::with(['orderItems.product.promotions', 'user'])->find($orderId);
            
            if (!$order) {
                return redirect()->route('shop.index')->with('error', 'Đơn hàng không tồn tại');
            }
            
            // Kiểm tra quyền truy cập
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập');
            }
            
            // Đảm bảo chỉ chủ sở hữu đơn hàng hoặc admin mới có thể thanh toán
            if ($user->id !== $order->user_id && $user->role !== 'admin') {
                return redirect()->route('orders.my')->with('error', 'Không có quyền truy cập đơn hàng này');
            }

            // Kiểm tra trạng thái đơn hàng
            if ($order->payment_status === 'paid') {
                return redirect()->route('orders.show', $order->id)->with('info', 'Đơn hàng này đã được thanh toán');
            }

            return view('payments.index', compact('order'));
            
        } catch (\Exception $e) {
            Log::error('Payment index error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('orders.my')->with('error', 'Có lỗi xảy ra trong quá trình xử lý');
        }
    }

    /**
     * Xử lý thanh toán VNPay
     */
    public function vnpayPayment(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id'
            ]);

            $order = Order::findOrFail($request->order_id);
            
            // Kiểm tra quyền truy cập - đảm bảo chỉ chủ sở hữu đơn hàng mới có thể thanh toán
            $currentUserId = Auth::id();
            if ($currentUserId !== $order->user_id) {
                return redirect()->route('orders.my')->with('error', 'Không có quyền thanh toán đơn hàng này');
            }

            // Kiểm tra trạng thái đơn hàng
            if ($order->payment_status === 'paid') {
                return redirect()->route('orders.show', $order->id)->with('info', 'Đơn hàng này đã được thanh toán');
            }

            // Cập nhật phương thức thanh toán
            $order->update([
                'payment_method' => 'vnpay',
                'payment_status' => 'pending'
            ]);

            // Tạo URL thanh toán VNPay
            $paymentUrl = $this->vnPayService->createPaymentUrl(
                $order->id,
                $order->total,
                "Thanh toán đơn hàng #{$order->id} - " . $order->user->name,
                'vn', // locale
                '', // bankCode
                $request->ip()
            );

            Log::info('VNPay payment initiated', [
                'order_id' => $order->id,
                'user_id' => $currentUserId,
                'amount' => $order->total
            ]);

            return redirect($paymentUrl);
            
        } catch (\Exception $e) {
            Log::error('VNPay payment error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'order_id' => $request->order_id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('payment.index', ['order_id' => $request->order_id])
                ->with('error', 'Có lỗi xảy ra trong quá trình tạo thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * Xử lý thanh toán khi nhận hàng (COD)
     */
    public function codPayment(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id'
            ]);

            $order = Order::findOrFail($request->order_id);
            
            // Kiểm tra quyền truy cập - đảm bảo chỉ chủ sở hữu đơn hàng mới có thể chọn COD
            $currentUserId = Auth::id();
            if ($currentUserId !== $order->user_id) {
                return redirect()->route('orders.my')->with('error', 'Không có quyền thay đổi phương thức thanh toán cho đơn hàng này');
            }

            // Kiểm tra trạng thái đơn hàng
            if ($order->payment_status === 'paid') {
                return redirect()->route('orders.show', $order->id)->with('info', 'Đơn hàng này đã được thanh toán');
            }

            // Cập nhật phương thức thanh toán
            $order->update([
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'status' => 'confirmed'
            ]);

            Log::info('COD payment selected', [
                'order_id' => $order->id,
                'user_id' => $currentUserId
            ]);

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Đơn hàng đã được xác nhận. Bạn sẽ thanh toán khi nhận hàng.');
                
        } catch (\Exception $e) {
            Log::error('COD payment error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'order_id' => $request->order_id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('payment.index', ['order_id' => $request->order_id])
                ->with('error', 'Có lỗi xảy ra trong quá trình chọn phương thức thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * Xử lý callback từ VNPay
     */
    public function vnpayReturn(Request $request)
    {
        try {
            $inputData = $request->all();
            
            // Log callback data để debug
            Log::info('VNPay callback received', [
                'data' => $inputData,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Validate response từ VNPay
            if (!$this->vnPayService->validateResponse($inputData)) {
                Log::error('VNPay response validation failed', ['data' => $inputData]);
                return redirect()->route('orders.my')
                    ->with('error', 'Giao dịch không hợp lệ. Vui lòng liên hệ hỗ trợ nếu bạn đã thanh toán.');
            }

            $orderId = $inputData['vnp_TxnRef'] ?? null;
            $responseCode = $inputData['vnp_ResponseCode'] ?? null;
            $amount = ($inputData['vnp_Amount'] ?? 0) / 100; // VNPay trả về amount * 100
            $transactionId = $inputData['vnp_TransactionNo'] ?? null;

            Log::info('VNPay processing order', [
                'order_id' => $orderId,
                'response_code' => $responseCode,
                'amount' => $amount,
                'transaction_id' => $transactionId
            ]);

            if (!$orderId) {
                Log::error('Missing order ID in VNPay callback');
                return redirect()->route('orders.my')
                    ->with('error', 'Không tìm thấy thông tin đơn hàng.');
            }

            $order = Order::find($orderId);
            if (!$order) {
                Log::error('Order not found for VNPay callback', ['order_id' => $orderId]);
                return redirect()->route('orders.my')
                    ->with('error', 'Không tìm thấy đơn hàng.');
            }

            DB::beginTransaction();
            try {
                if ($responseCode === '00') {
                    // Thanh toán thành công
                    $order->update([
                        'payment_status' => 'paid',
                        'payment_method' => 'vnpay',
                        'payment_date' => now(),
                        'vnpay_transaction_id' => $transactionId,
                        'status' => 'confirmed'
                    ]);
                    
                    // Trừ số lượng sản phẩm trong kho
                    $this->inventoryService->deductInventory($order);
                    
                    Log::info('VNPay payment successful', [
                        'order_id' => $orderId,
                        'transaction_id' => $transactionId,
                        'amount' => $amount
                    ]);
                    
                    $message = 'Thanh toán thành công! Cảm ơn bạn đã mua hàng.';
                    $type = 'success';
                } else {
                    // Thanh toán thất bại
                    $order->update([
                        'payment_status' => 'failed',
                        'payment_method' => 'vnpay',
                        'vnpay_transaction_id' => $transactionId
                    ]);
                    
                    $status = $this->vnPayService->getTransactionStatus($responseCode);
                    
                    Log::warning('VNPay payment failed', [
                        'order_id' => $orderId,
                        'response_code' => $responseCode,
                        'status' => $status,
                        'transaction_id' => $transactionId
                    ]);
                    
                    $message = "Thanh toán thất bại. Lý do: {$status}";
                    $type = 'error';
                }
                
                DB::commit();
                
                return redirect()->route('orders.show', $order->id)
                    ->with($type, $message);
                    
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('VNPay payment processing error: ' . $e->getMessage(), [
                    'order_id' => $orderId,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('VNPay callback error: ' . $e->getMessage(), [
                'input_data' => $inputData ?? [],
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('orders.my')
                ->with('error', 'Có lỗi xảy ra trong quá trình xử lý thanh toán. Vui lòng kiểm tra lại đơn hàng của bạn.');
        }
    }    /**
     * Admin cập nhật trạng thái thanh toán COD
     */
     public function updateCodStatus(Request $request, $orderId)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,failed'
        ]);

        $order = Order::findOrFail($orderId);
        
        // Chỉ admin mới có thể cập nhật COD status
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Chỉ admin mới có quyền cập nhật trạng thái thanh toán COD');
        }

        // Chỉ cập nhật được đơn hàng COD
        if ($order->payment_method !== 'cod') {
            return response()->json([
                'error' => 'Chỉ có thể cập nhật đơn hàng thanh toán khi nhận hàng.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $order->update([
                'payment_status' => $request->payment_status,
                'payment_date' => $request->payment_status === 'paid' ? now() : null
            ]);

            // Nếu thanh toán thành công, trừ kho
            if ($request->payment_status === 'paid') {
                $this->inventoryService->deductInventory($order);
            }

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Cập nhật trạng thái thanh toán thành công.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('COD payment update error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}