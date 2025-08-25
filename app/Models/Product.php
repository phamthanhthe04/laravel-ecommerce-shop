<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'price', 'category_id', 'stock_quantity', 'image'];
    
    protected $casts = [
        'price' => 'decimal:2',
    ];
    
    // Accessor để hỗ trợ cả stock và stock_quantity
    public function getStockAttribute()
    {
        return $this->stock_quantity;
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    // Relationship với OrderItems
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // Relationship với Promotions
    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }
    
    /**
     * Get active promotions for this product
     */
    public function activePromotions(): HasMany
    {
        return $this->promotions()->active();
    }
    
    /**
     * Get the best active promotion (highest discount)
     */
    public function getBestActivePromotion()
    {
        $activePromotions = $this->activePromotions()->get();
        
        if ($activePromotions->isEmpty()) {
            return null;
        }
        
        // Tính toán discount amount cho mỗi promotion và chọn cái cao nhất
        return $activePromotions->sortByDesc(function ($promotion) {
            if ($promotion->discount_type === 'percentage') {
                return $this->price * ($promotion->discount_value / 100);
            } else {
                return min($promotion->discount_value, $this->price);
            }
        })->first();
    }
    
    /**
     * Get discounted price if promotion exists
     */
    public function getDiscountedPrice(): float
    {
        $promotion = $this->getBestActivePromotion();
        
        if ($promotion) {
            if ($promotion->discount_type === 'percentage') {
                return $this->price * (1 - ($promotion->discount_value / 100));
            } else {
                return max(0, $this->price - $promotion->discount_value);
            }
        }
        
        return $this->price;
    }
    
    /**
     * Check if product has active promotion
     */
    public function hasActivePromotion(): bool
    {
        return $this->activePromotions()->exists();
    }
}
