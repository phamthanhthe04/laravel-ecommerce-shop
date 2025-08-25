<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relationship với Order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Relationship với Product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Tính tổng tiền cho item này
    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}
