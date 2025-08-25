<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePromotionRequest;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    /**
     * Display a listing of the promotions.
     */
    public function index(): View
    {
        $promotions = Promotion::with('product')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create(): View
    {
        $products = Product::orderBy('name')->get();
        return view('promotions.create', compact('products'));
    }

    /**
     * Store a newly created promotion in storage.
     */
    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Kiểm tra xem sản phẩm đã có khuyến mãi active trong khoảng thời gian này chưa
        $existingPromotion = Promotion::where('product_id', $validated['product_id'])
            ->where('is_active', true)
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                          ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($existingPromotion) {
            return back()
                ->withInput()
                ->withErrors(['product_id' => 'Sản phẩm này đã có khuyến mãi trong khoảng thời gian được chọn.']);
        }

        Promotion::create($validated);

        return redirect()->route('promotions.index')
            ->with('success', 'Khuyến mãi đã được tạo thành công!');
    }

    /**
     * Display the specified promotion.
     */
    public function show(Promotion $promotion): View
    {
        $promotion->load('product');
        return view('promotions.show', compact('promotion'));
    }

    /**
     * Show the form for editing the specified promotion.
     */
    public function edit(Promotion $promotion): View
    {
        $products = Product::orderBy('name')->get();
        return view('promotions.edit', compact('promotion', 'products'));
    }

    /**
     * Update the specified promotion in storage.
     */
    public function update(StorePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $validated = $request->validated();

        // Kiểm tra conflict với các promotion khác (ngoại trừ promotion hiện tại)
        $existingPromotion = Promotion::where('product_id', $validated['product_id'])
            ->where('id', '!=', $promotion->id)
            ->where('is_active', true)
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                          ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($existingPromotion) {
            return back()
                ->withInput()
                ->withErrors(['product_id' => 'Sản phẩm này đã có khuyến mãi trong khoảng thời gian được chọn.']);
        }

        $promotion->update($validated);

        return redirect()->route('promotions.index')
            ->with('success', 'Khuyến mãi đã được cập nhật thành công!');
    }

    /**
     * Remove the specified promotion from storage.
     */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()->route('promotions.index')
            ->with('success', 'Khuyến mãi đã được xóa thành công!');
    }

    /**
     * Toggle promotion status (active/inactive).
     */
    public function toggleStatus(Promotion $promotion): JsonResponse
    {
        $promotion->update(['is_active' => !$promotion->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $promotion->is_active,
            'message' => $promotion->is_active ? 'Khuyến mãi đã được kích hoạt!' : 'Khuyến mãi đã được tắt!'
        ]);
    }

    /**
     * Apply promotion to cart item.
     */
    public function applyPromotion(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $bestPromotion = $product->getBestActivePromotion();

        if (!$bestPromotion) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm này hiện không có khuyến mãi nào.'
            ]);
        }

        $originalPrice = $product->price;
        $discountedPrice = $product->getDiscountedPrice();
        $totalDiscount = ($originalPrice - $discountedPrice) * $request->quantity;

        return response()->json([
            'success' => true,
            'promotion' => [
                'id' => $bestPromotion->id,
                'name' => $bestPromotion->name,
                'discount_percentage' => $bestPromotion->discount_percentage
            ],
            'pricing' => [
                'original_price' => $originalPrice,
                'discounted_price' => $discountedPrice,
                'total_discount' => $totalDiscount,
                'final_total' => $discountedPrice * $request->quantity
            ]
        ]);
    }

    /**
     * Get active promotions for API.
     */
    public function getActivePromotions(): JsonResponse
    {
        $promotions = Promotion::active()
            ->with('product:id,name,price')
            ->get()
            ->map(function ($promotion) {
                return [
                    'id' => $promotion->id,
                    'name' => $promotion->name,
                    'discount_percentage' => $promotion->discount_percentage,
                    'product' => [
                        'id' => $promotion->product->id,
                        'name' => $promotion->product->name,
                        'original_price' => $promotion->product->price,
                        'discounted_price' => $promotion->product->getDiscountedPrice()
                    ],
                    'ends_at' => $promotion->end_date->format('Y-m-d H:i:s')
                ];
            });

        return response()->json([
            'success' => true,
            'promotions' => $promotions
        ]);
    }
}
