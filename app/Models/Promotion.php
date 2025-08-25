<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'product_id',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_value' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that owns the promotion.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include active promotions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
    }

    /**
     * Scope a query to only include promotions for a specific product.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $productId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Check if the promotion is currently active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->is_active && $this->start_date <= $now && $this->end_date >= $now;
    }

    /**
     * Get the status of the promotion.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        $now = Carbon::now();
        
        if (!$this->is_active) {
            return 'inactive';
        } elseif ($this->start_date > $now) {
            return 'scheduled';
        } elseif ($this->end_date < $now) {
            return 'expired';
        } else {
            return 'active';
        }
    }

    /**
     * Calculate discounted price for a given original price.
     *
     * @param float $originalPrice
     * @return float
     */
    public function calculateDiscountedPrice(float $originalPrice): float
    {
        if ($this->discount_type === 'percentage') {
            $discountAmount = ($originalPrice * $this->discount_value) / 100;
            return round($originalPrice - $discountAmount, 0);
        } else {
            return round(max(0, $originalPrice - $this->discount_value), 0);
        }
    }

    /**
     * Calculate discount amount for a given original price.
     *
     * @param float $originalPrice
     * @return float
     */
    public function calculateDiscountAmount(float $originalPrice): float
    {
        if ($this->discount_type === 'percentage') {
            return round(($originalPrice * $this->discount_value) / 100, 0);
        } else {
            return round(min($this->discount_value, $originalPrice), 0);
        }
    }

    /**
     * Calculate discount amount for the promotion's product.
     *
     * @return float
     */
    public function getProductDiscountAmount(): float
    {
        if (!$this->relationLoaded('product')) {
            $this->load('product');
        }
        return $this->calculateDiscountAmount($this->product->price);
    }

    /**
     * Calculate discounted price for the promotion's product.
     *
     * @return float
     */
    public function getDiscountedProductPrice(): float
    {
        if (!$this->relationLoaded('product')) {
            $this->load('product');
        }
        return $this->calculateDiscountedPrice($this->product->price);
    }
}