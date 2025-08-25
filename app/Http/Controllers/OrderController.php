<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index()
    {
        $orders = Order::with(['user', 'orderItems.product'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
        
        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Load relationships
        $order->load(['user', 'orderItems.product']);
        
        // Kiểm tra quyền truy cập
        $user = Auth::user();
        if (!$user || ($user->id !== $order->user_id && ($user->role !== 'admin'))) {
            abort(403);
        }
        
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        // Validate request
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipping,completed,cancelled'
        ]);

        // Update order status
        $order->update($validated);

        return redirect()->route('admin.orders')->with('success', 'Đã cập nhật trạng thái đơn hàng thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Display orders for the authenticated user.
     */
    public function myOrders()
    {
        $orders = Order::with(['orderItems.product'])
                      ->where('user_id', Auth::id())
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);
        
        return view('orders.my-orders', compact('orders'));
    }
}