<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Trừ số lượng sản phẩm khi thanh toán thành công
     */
    public function deductInventory(Order $order)
    {
        DB::beginTransaction();
        
        try {
            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                
                if (!$product) {
                    throw new \Exception("Product with ID {$item->product_id} not found");
                }
                
                // Kiểm tra số lượng còn lại
                if ($product->stock_quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock_quantity}, Required: {$item->quantity}");
                }
                
                // Trừ số lượng
                $product->decrement('stock_quantity', $item->quantity);
                
                Log::info("Inventory deducted", [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_deducted' => $item->quantity,
                    'remaining_stock' => $product->fresh()->stock_quantity,
                    'order_id' => $order->id
                ]);
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to deduct inventory for order {$order->id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Hoàn lại số lượng sản phẩm khi hủy đơn hàng
     */
    public function restoreInventory(Order $order)
    {
        DB::beginTransaction();
        
        try {
            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                
                if ($product) {
                    $product->increment('stock_quantity', $item->quantity);
                    
                    Log::info("Inventory restored", [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity_restored' => $item->quantity,
                        'new_stock' => $product->fresh()->stock_quantity,
                        'order_id' => $order->id
                    ]);
                }
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to restore inventory for order {$order->id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Kiểm tra tồn kho trước khi đặt hàng
     */
    public function checkInventoryAvailability(array $cartItems)
    {
        $errors = [];
        
        foreach ($cartItems as $productId => $quantity) {
            $product = Product::find($productId);
            
            if (!$product) {
                $errors[] = "Product with ID {$productId} not found";
                continue;
            }
            
            if ($product->stock_quantity < $quantity) {
                $errors[] = "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}, Requested: {$quantity}";
            }
        }
        
        return [
            'available' => empty($errors),
            'errors' => $errors
        ];
    }
}
