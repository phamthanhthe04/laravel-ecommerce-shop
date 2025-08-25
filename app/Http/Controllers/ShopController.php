<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'promotions']);

        // Lọc theo category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Tìm kiếm theo tên
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sắp xếp
        switch ($request->sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12);
        $categories = Category::all();

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'promotions']);
        
        $relatedProducts = Product::with(['category', 'promotions'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('stock_quantity', '>', 0)
            ->take(4)
            ->get();

        return view('shop.show', compact('product', 'relatedProducts'));
    }
}
