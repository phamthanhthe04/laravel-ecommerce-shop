<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MockPaymentController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }
    /**
     * Hiển thị form thanh toán giả lập
     */
    public function showPaymentForm($orderId)
    {
        try {
            $order = Order::with(['orderItems.product', 'user'])->findOrFail($orderId);
            
            // Kiểm tra quyền truy cập - đảm bảo chỉ chủ sở hữu đơn hàng mới có thể thanh toán
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục');
            }
            
            if ($user->id !== $order->user_id && $user->role !== 'admin') {
                return redirect()->route('orders.my')->with('error', 'Bạn không có quyền truy cập đơn hàng này.');
            }
            
            // Kiểm tra trạng thái đơn hàng
            if ($order->payment_status === 'paid') {
                return redirect()->route('orders.show', $order->id)
                    ->with('info', 'Đơn hàng này đã được thanh toán.');
            }
            
            if ($order->payment_status !== 'pending') {
                return redirect()->route('orders.show', $order->id)
                    ->with('error', 'Đơn hàng này không thể thanh toán (Trạng thái: ' . $order->payment_status . ')');
            }
            
            return view('payments.mock-payment', compact('order'));
            
        } catch (\Exception $e) {
            Log::error('Mock payment form error: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('orders.my')->with('error', 'Có lỗi xảy ra khi tải trang thanh toán');
        }
    }

    /**
     * Xử lý kết quả thanh toán giả lập
     */
    public function processPayment(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'action' => 'required|in:success,fail',
                'card_number' => 'required|string',
                'cardholder_name' => 'required|string'
            ]);

            $order = Order::findOrFail($request->order_id);
            
            // Kiểm tra quyền truy cập - đảm bảo chỉ chủ sở hữu đơn hàng mới có thể thanh toán
            $currentUserId = Auth::id();
            if (!$currentUserId || $currentUserId !== $order->user_id) {
                return redirect()->route('orders.my')->with('error', 'Không có quyền thanh toán đơn hàng này');
            }

            // Kiểm tra trạng thái đơn hàng
            if ($order->payment_status === 'paid') {
                return redirect()->route('orders.show', $order->id)
                    ->with('info', 'Đơn hàng này đã được thanh toán');
            }

            $action = $request->action;
            $cardNumber = preg_replace('/\D/', '', $request->card_number); // Chỉ lấy số
            $cardholderName = $request->cardholder_name;

            // Tạo transaction ID giả lập
            $mockTransactionId = 'MOCK_' . strtoupper(substr($action, 0, 4)) . '_' . time();

            DB::beginTransaction();
            try {
                if ($action === 'success') {
                    $order->update([
                        'payment_status' => 'paid',
                        'payment_method' => 'mock_card',
                        'payment_date' => now(),
                        'mock_transaction_id' => $mockTransactionId,
                        'mock_card_last4' => substr($cardNumber, -4),
                        'status' => 'confirmed'
                    ]);
                    
                    // Trừ số lượng sản phẩm trong kho
                    $this->inventoryService->deductInventory($order);
                    
                    Log::info('Mock payment successful', [
                        'order_id' => $order->id,
                        'user_id' => $currentUserId,
                        'transaction_id' => $mockTransactionId
                    ]);
                    
                    DB::commit();
                    return redirect()->route('orders.show', $order->id)
                        ->with('success', "✅ Thanh toán thành công! Mã giao dịch: {$mockTransactionId}");
                } else {
                    $order->update([
                        'payment_status' => 'failed',
                        'payment_method' => 'mock_card',
                        'mock_transaction_id' => $mockTransactionId,
                        'mock_card_last4' => substr($cardNumber, -4)
                    ]);
                    
                    Log::info('Mock payment failed (simulated)', [
                        'order_id' => $order->id,
                        'user_id' => $currentUserId,
                        'transaction_id' => $mockTransactionId
                    ]);
                    
                    DB::commit();
                    return redirect()->route('orders.show', $order->id)
                        ->with('error', "❌ Thanh toán thất bại! Mã giao dịch: {$mockTransactionId}");
                }
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Mock payment error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'order_id' => $request->order_id ?? null,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('payment.index', ['order_id' => $request->order_id])
                ->with('error', 'Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * Xử lý thanh toán nhanh (không cần form)
     */
    public function quickPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'result' => 'required|in:success,fail'
        ]);

        $order = Order::findOrFail($request->order_id);
        
        if (Auth::id() !== $order->user_id) {
            abort(403);
        }

        $result = $request->result;
        $mockTransactionId = 'MOCK_QUICK_' . strtoupper(substr($result, 0, 4)) . '_' . time();

        DB::beginTransaction();
        try {
            if ($result === 'success') {
                $order->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'mock_quick',
                    'payment_date' => now(),
                    'mock_transaction_id' => $mockTransactionId,
                    'status' => 'confirmed'
                ]);
                
                // Trừ số lượng sản phẩm trong kho
                $this->inventoryService->deductInventory($order);
                
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Thanh toán thành công!',
                    'transaction_id' => $mockTransactionId
                ]);
            } else {
                $order->update([
                    'payment_status' => 'failed',
                    'payment_method' => 'mock_quick',
                    'mock_transaction_id' => $mockTransactionId
                ]);
                
                DB::commit();
                return response()->json([
                    'success' => false,
                    'message' => 'Thanh toán thất bại!',
                    'transaction_id' => $mockTransactionId
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Quick mock payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
}