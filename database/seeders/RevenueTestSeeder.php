<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;

class RevenueTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy user và product để tạo đơn hàng test
        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->info('Cần có user và product trước khi chạy seeder này');
            return;
        }

        // Tạo đơn hàng cho 30 ngày gần đây
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Tạo 1-5 đơn hàng ngẫu nhiên mỗi ngày
            $orderCount = rand(1, 5);
            
            for ($j = 0; $j < $orderCount; $j++) {
                $user = $users->random();
                $randomProducts = $products->random(rand(1, 3));
                
                $order = Order::create([
                    'user_id' => $user->id,
                    'total' => 0,
                    'status' => 'pending',
                    'payment_status' => 'paid', // Đánh dấu đã thanh toán để tính doanh thu
                    'payment_method' => ['vnpay', 'cod', 'cash'][rand(0, 2)],
                    'created_at' => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                    'updated_at' => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                ]);

                $total = 0;
                
                foreach ($randomProducts as $product) {
                    $quantity = rand(1, 3);
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $product->price,
                    ]);
                    
                    $total += $orderItem->price * $orderItem->quantity;
                }
                
                $order->update(['total' => $total]);
            }
        }

        $this->command->info('Đã tạo dữ liệu test cho biểu đồ doanh thu');
    }
}
