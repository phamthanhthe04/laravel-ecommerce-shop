<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy các sản phẩm để tạo khuyến mãi
        $products = Product::take(5)->get();

        foreach ($products as $index => $product) {
            Promotion::create([
                'name' => 'Khuyến mãi ' . $product->name,
                'description' => 'Giảm giá đặc biệt cho sản phẩm ' . $product->name . '. Cơ hội mua sắm với giá ưu đãi!',
                'discount_percentage' => 10 + ($index * 10), // 10%, 20%, 30%, 40%, 50%
                'product_id' => $product->id,
                'start_date' => now(),
                'end_date' => now()->addDays(30),
                'is_active' => true
            ]);
        }

        // Tạo thêm một vài khuyến mãi không hoạt động và đã hết hạn
        if ($products->count() >= 2) {
            // Khuyến mãi không hoạt động
            Promotion::create([
                'name' => 'Khuyến mãi tạm dừng',
                'description' => 'Khuyến mãi này đang tạm dừng',
                'discount_percentage' => 25,
                'product_id' => $products[0]->id,
                'start_date' => now(),
                'end_date' => now()->addDays(7),
                'is_active' => false
            ]);

            // Khuyến mãi đã hết hạn
            Promotion::create([
                'name' => 'Khuyến mãi đã hết hạn',
                'description' => 'Khuyến mãi này đã kết thúc',
                'discount_percentage' => 35,
                'product_id' => $products[1]->id,
                'start_date' => now()->subDays(10),
                'end_date' => now()->subDays(5),
                'is_active' => true
            ]);

            // Khuyến mãi sắp diễn ra
            Promotion::create([
                'name' => 'Khuyến mãi sắp diễn ra',
                'description' => 'Khuyến mãi này sẽ bắt đầu trong tương lai',
                'discount_percentage' => 20,
                'product_id' => $products[1]->id,
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(15),
                'is_active' => true
            ]);
        }
    }
}
