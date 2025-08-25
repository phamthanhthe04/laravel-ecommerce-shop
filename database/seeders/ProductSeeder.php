<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Điện thoại iPhone 15 Pro màn hình 6.1 inch',
                'price' => 25990000,
                'category_id' => 1,
                'stock_quantity' => 50,
                'image' => 'https://picsum.photos/300/200?random=1'
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Điện thoại Samsung Galaxy S24 màn hình 6.2 inch',
                'price' => 20990000,
                'category_id' => 1,
                'stock_quantity' => 30,
                'image' => 'https://picsum.photos/300/200?random=2'
            ],
            [
                'name' => 'MacBook Pro M3',
                'description' => 'Laptop MacBook Pro với chip M3',
                'price' => 45990000,
                'category_id' => 2,
                'stock_quantity' => 15,
                'image' => 'https://picsum.photos/300/200?random=3'
            ],
            [
                'name' => 'Dell XPS 13',
                'description' => 'Laptop Dell XPS 13 màn hình 13.3 inch',
                'price' => 25990000,
                'category_id' => 2,
                'stock_quantity' => 20,
                'image' => 'https://picsum.photos/300/200?random=4'
            ],
            [
                'name' => 'iPad Pro 12.9',
                'description' => 'iPad Pro màn hình 12.9 inch với chip M2',
                'price' => 29990000,
                'category_id' => 4,
                'stock_quantity' => 25,
                'image' => 'https://picsum.photos/300/200?random=5'
            ],
            [
                'name' => 'Apple Watch Series 9',
                'description' => 'Đồng hồ thông minh Apple Watch Series 9',
                'price' => 9990000,
                'category_id' => 5,
                'stock_quantity' => 40,
                'image' => 'https://picsum.photos/300/200?random=6'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
