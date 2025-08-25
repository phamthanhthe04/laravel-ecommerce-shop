<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Điện thoại'],
            ['name' => 'Laptop'],
            ['name' => 'Phụ kiện'],
            ['name' => 'Tablet'],
            ['name' => 'Đồng hồ'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
